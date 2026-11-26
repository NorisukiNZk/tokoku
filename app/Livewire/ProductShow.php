<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\StoreSetting;
use Livewire\Component;

class ProductShow extends Component
{
    public Product $product;
    public ?string $activeImage = null;

    public function mount(string $slug): void
    {
        $this->product = Product::query()
            ->with('category')
            ->where('slug', $slug)
            ->firstOrFail();

        $this->activeImage = $this->product->primaryImageUrl();
    }

    public function selectImage(string $url): void
    {
        $this->activeImage = $url;
    }

    public function render()
    {
        $galleryUrls = collect([$this->product->primaryImageUrl()])
            ->merge(collect($this->product->gallery_images ?? [])->map(
                fn ($img) => str_starts_with($img, 'http') || str_starts_with($img, '/')
                    ? $img
                    : '/storage/' . ltrim($img, '/')
            ))
            ->filter()
            ->unique()
            ->values();

        $related = Product::query()
            ->where('category_id', $this->product->category_id)
            ->whereKeyNot($this->product->id)
            ->where('show_in_marketplace', true)
            ->where('availability_status', 'available')
            ->limit(4)
            ->get();

        $store = StoreSetting::current();
        $negoLabel = $this->product->is_negotiable ? 'Bisa Nego' : 'Harga Nett';
        $warrantyText = $this->product->warranty_info ?: 'Garansi cek langsung di workshop';
        $metaDesc = "{$this->product->formattedPrice()} ({$negoLabel}) · Kondisi {$this->product->conditionLabel()} · {$warrantyText} · Lokasi {$this->product->location_area}.";

        return view('livewire.product-show', [
            'store' => $store,
            'galleryUrls' => $galleryUrls,
            'related' => $related,
        ])
            ->title($this->product->name . ' (' . $this->product->formattedPrice() . ') - ' . $store->store_name)
            ->layoutData([
                'metaDescription' => $metaDesc,
                'metaImage' => $this->product->primaryImageUrl(),
                'metaType' => 'product',
                'canonicalUrl' => route('product.show', $this->product->slug),
            ]);
    }
}
