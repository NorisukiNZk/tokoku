@php
    $store = \App\Models\StoreSetting::current();
    $roleLabel = $role ?? 'Admin';
@endphp
<div class="tk-brand-wrap">
    <span class="tk-brand-mark" aria-hidden="true">{{ strtoupper(substr($store->store_name, 0, 1)) }}</span>
    <span class="tk-brand-text">
        <span class="tk-brand-name">{{ $store->store_name }}</span>
        <span class="tk-brand-role">{{ $roleLabel }}</span>
    </span>
</div>
