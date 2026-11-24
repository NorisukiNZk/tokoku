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
                    : \Illuminate\Support\Facades\Storage::disk('public')->url($img)
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

        return view('livewire.product-show', [
            'store' => StoreSetting::current(),
            'galleryUrls' => $galleryUrls,
            'related' => $related,
        ])->title($this->product->name . ' - ' . StoreSetting::current()->store_name);
    }
}
