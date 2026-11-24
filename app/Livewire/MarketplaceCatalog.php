<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class MarketplaceCatalog extends Component
{
    use WithPagination;

    #[Url(as: 'cari', except: '')]
    public string $search = '';

    #[Url(as: 'kategori', except: '')]
    public string $categorySlug = '';

    #[Url(as: 'kondisi', except: 'all')]
    public string $conditionFilter = 'all';

    #[Url(as: 'nego', except: false)]
    public bool $onlyNegotiable = false;

    #[Url(as: 'sembunyikan_terjual', except: false)]
    public bool $hideSold = false;

    #[Url(as: 'urut', except: 'terbaru')]
    public string $sortBy = 'terbaru';

    public string $quickTicketNumber = '';
    public string $quickTicketPin = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategorySlug(): void
    {
        $this->resetPage();
    }

    public function updatedConditionFilter(): void
    {
        $this->resetPage();
    }

    public function updatedOnlyNegotiable(): void
    {
        $this->resetPage();
    }

    public function updatedHideSold(): void
    {
        $this->resetPage();
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'categorySlug', 'conditionFilter', 'onlyNegotiable', 'hideSold', 'sortBy']);
        $this->conditionFilter = 'all';
        $this->sortBy = 'terbaru';
        $this->resetPage();
    }

    public function quickCheckTicket(): mixed
    {
        $ticket = trim($this->quickTicketNumber);
        $pin = trim($this->quickTicketPin);

        return $this->redirect(route('service.index', [
            'tiket' => $ticket,
            'pin' => $pin,
        ]));
    }

    public function render()
    {
        $categories = Category::query()
            ->withCount(['products' => fn ($q) => $q->where('show_in_marketplace', true)])
            ->orderBy('sort_order')
            ->get();

        $query = Product::query()
            ->with('category')
            ->where('show_in_marketplace', true);

        if ($this->search !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($sub) use ($term) {
                $sub->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhere('brand', 'like', $term)
                    ->orWhere('condition_notes', 'like', $term);
            });
        }

        if ($this->categorySlug !== '') {
            $query->whereHas('category', fn ($q) => $q->where('slug', $this->categorySlug));
        }

        if ($this->conditionFilter === 'new') {
            $query->where('condition', 'new');
        } elseif ($this->conditionFilter === 'used_normal') {
            $query->whereIn('condition', ['like_new', 'good_used']);
        } elseif ($this->conditionFilter === 'minus_parts') {
            $query->where('condition', 'minus_parts');
        }

        if ($this->onlyNegotiable) {
            $query->where('is_negotiable', true);
        }

        if ($this->hideSold) {
            $query->where('availability_status', 'available')->where('stock', '>', 0);
        }

        match ($this->sortBy) {
            'termurah' => $query->orderBy('price', 'asc'),
            'termahal' => $query->orderBy('price', 'desc'),
            default => $query->orderByRaw("CASE WHEN availability_status = 'available' THEN 0 ELSE 1 END")->orderByDesc('updated_at'),
        };

        return view('livewire.marketplace-catalog', [
            'store' => StoreSetting::current(),
            'categories' => $categories,
            'products' => $query->paginate(12),
        ])->title(StoreSetting::current()->store_name . ' - Etalase Part PC Baru & Bekas');
    }
}
