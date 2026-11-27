@php
    $before = is_array($record->before_values) ? $record->before_values : [];
    $after = is_array($record->after_values) ? $record->after_values : [];
    $allKeys = array_values(array_unique(array_merge(array_keys($before), array_keys($after))));

    $formatVal = static function ($value): string {
        if ($value === null || $value === '') {
            return '(kosong)';
        }
        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }
        if (is_array($value)) {
            return (string) json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    };
@endphp

<div style="display: flex; flex-direction: column; gap: 1rem; font-size: 0.875rem; color: var(--tk-ink);">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(11rem, 1fr)); gap: 0.75rem; padding: 0.9rem 1rem; border-radius: 0.75rem; background-color: var(--tk-subtle); border: 1px solid var(--tk-border);">
        <div>
            <div style="font-size: 0.72rem; font-weight: 600; color: var(--tk-muted); text-transform: uppercase;">Waktu Tercatat</div>
            <div class="tk-mono" style="font-weight: 700; margin-top: 0.15rem;">{{ $record->created_at?->format('d M Y, H:i:s') ?? '-' }}</div>
        </div>
        <div>
            <div style="font-size: 0.72rem; font-weight: 600; color: var(--tk-muted); text-transform: uppercase;">Pelaku</div>
            <div style="font-weight: 700; margin-top: 0.15rem;">{{ $record->actor_name }}</div>
        </div>
        <div>
            <div style="font-size: 0.72rem; font-weight: 600; color: var(--tk-muted); text-transform: uppercase;">Jenis Aksi</div>
            <div style="font-weight: 700; margin-top: 0.15rem;">{{ $record->actionLabel() }}</div>
        </div>
        <div>
            <div style="font-size: 0.72rem; font-weight: 600; color: var(--tk-muted); text-transform: uppercase;">Alamat IP</div>
            <div class="tk-mono" style="font-weight: 600; margin-top: 0.15rem;">{{ $record->ip_address ?: '-' }}</div>
        </div>
    </div>

    <div style="padding: 0.85rem 1rem; border-radius: 0.75rem; border: 1px solid var(--tk-border); background-color: var(--tk-surface);">
        <div style="font-size: 0.72rem; font-weight: 600; color: var(--tk-muted); text-transform: uppercase; margin-bottom: 0.25rem;">Ringkasan</div>
        <div style="line-height: 1.5;">{{ $record->description }}</div>
    </div>

    @if(count($allKeys) > 0)
        <div style="border: 1px solid var(--tk-border); border-radius: 0.75rem; overflow: hidden;">
            <div style="padding: 0.65rem 1rem; background-color: var(--tk-subtle); border-bottom: 1px solid var(--tk-border); font-size: 0.78rem; font-weight: 700;">
                Perbandingan Nilai Sebelum &amp; Sesudah
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.82rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--tk-border); text-align: left;">
                            <th style="padding: 0.6rem 0.9rem; color: var(--tk-muted); font-weight: 700;">Bidang</th>
                            <th style="padding: 0.6rem 0.9rem; color: var(--tk-muted); font-weight: 700;">Sebelum</th>
                            <th style="padding: 0.6rem 0.9rem; color: var(--tk-muted); font-weight: 700;">Sesudah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allKeys as $key)
                            <tr style="border-bottom: 1px solid var(--tk-border);">
                                <td class="tk-mono" style="padding: 0.6rem 0.9rem; font-weight: 700;">{{ $key }}</td>
                                <td style="padding: 0.6rem 0.9rem; color: var(--tk-err-fg); word-break: break-word;">{{ $formatVal($before[$key] ?? null) }}</td>
                                <td style="padding: 0.6rem 0.9rem; color: var(--tk-ok-fg); font-weight: 600; word-break: break-word;">{{ $formatVal($after[$key] ?? null) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="tk-empty">Tidak ada detail kolom sebelum/sesudah untuk catatan ini.</div>
    @endif
</div>
