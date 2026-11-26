<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:700,800" rel="stylesheet" />
<style>
    :root {
        --tk-canvas: #F4F6F9;
        --tk-surface: #FFFFFF;
        --tk-subtle: #EEF2F7;
        --tk-border: #DCE3EC;
        --tk-border-strong: #8494A7;
        --tk-ink: #0F172A;
        --tk-muted: #475569;
        --tk-accent: #C2410C;
        --tk-accent-hover: #9A3412;
        --tk-accent-fg: #FFFFFF;
        --tk-accent-soft: #FFF7ED;
        --tk-accent-soft-fg: #9A3412;
        --tk-ok-bg: #ECFDF5;
        --tk-ok-fg: #065F46;
        --tk-ok-bd: #34D399;
        --tk-warn-bg: #FFFBEB;
        --tk-warn-fg: #92400E;
        --tk-warn-bd: #FBBF24;
        --tk-err-bg: #FEF2F2;
        --tk-err-fg: #991B1B;
        --tk-err-bd: #F87171;
    }

    html.dark {
        --tk-canvas: #0B1120;
        --tk-surface: #162032;
        --tk-subtle: #1E293B;
        --tk-border: #2C3A52;
        --tk-border-strong: #7C8EA6;
        --tk-ink: #F8FAFC;
        --tk-muted: #CBD5E1;
        --tk-accent: #FB923C;
        --tk-accent-hover: #FDBA74;
        --tk-accent-fg: #0F172A;
        --tk-accent-soft: #2C190E;
        --tk-accent-soft-fg: #FDBA74;
        --tk-ok-bg: #064E3B;
        --tk-ok-fg: #A7F3D0;
        --tk-ok-bd: #10B981;
        --tk-warn-bg: #451A03;
        --tk-warn-fg: #FDE68A;
        --tk-warn-bd: #F59E0B;
        --tk-err-bg: #450A0A;
        --tk-err-fg: #FECACA;
        --tk-err-bd: #EF4444;
    }

    /* Refined Filament shell: Dark Charcoal Sidebar + Crisp Workspace */
    .fi-body {
        background-color: var(--tk-canvas) !important;
    }

    .fi-header:has(.fi-header-heading:empty) {
        display: none !important;
    }

    .fi-sidebar {
        background-color: #0F172A !important;
        border-inline-end: 1px solid #1E293B !important;
    }

    .fi-sidebar-header {
        background-color: #0F172A !important;
        border-bottom: 1px solid #1E293B !important;
        box-shadow: none !important;
    }

    .fi-sidebar .tk-brand-mark {
        background-color: #0B1220 !important;
        border: 1.5px solid #FB923C !important;
        color: #F8FAFC !important;
    }

    .fi-sidebar .tk-brand-name {
        color: #F8FAFC !important;
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        font-weight: 800 !important;
    }

    .fi-sidebar .tk-brand-role {
        color: #CBD5E1 !important;
    }

    .fi-sidebar-header .fi-icon-btn {
        color: #CBD5E1 !important;
    }

    .fi-sidebar-group-label {
        color: #94A3B8 !important;
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.05em !important;
        text-transform: uppercase !important;
    }

    .fi-sidebar-group-collapse-button {
        color: #94A3B8 !important;
    }

    .fi-sidebar-item:not(.fi-active) > .fi-sidebar-item-button .fi-sidebar-item-label,
    .fi-sidebar-item:not(.fi-active) > .fi-sidebar-item-button .fi-sidebar-item-icon {
        color: #CBD5E1 !important;
        transition: color 140ms ease !important;
    }

    .fi-sidebar-item:not(.fi-active) > .fi-sidebar-item-button:hover {
        background-color: #1E293B !important;
    }

    .fi-sidebar-item:not(.fi-active) > .fi-sidebar-item-button:hover .fi-sidebar-item-label,
    .fi-sidebar-item:not(.fi-active) > .fi-sidebar-item-button:hover .fi-sidebar-item-icon {
        color: #F8FAFC !important;
    }

    .fi-sidebar-item-active > .fi-sidebar-item-button,
    .fi-sidebar-item.fi-active > .fi-sidebar-item-button {
        background-color: #2C190E !important;
        border-inline-start: 3px solid #FB923C !important;
        border-radius: 0.65rem !important;
    }

    .fi-sidebar-item-active > .fi-sidebar-item-button .fi-sidebar-item-label,
    .fi-sidebar-item-active > .fi-sidebar-item-button .fi-sidebar-item-icon,
    .fi-sidebar-item.fi-active > .fi-sidebar-item-button .fi-sidebar-item-label,
    .fi-sidebar-item.fi-active > .fi-sidebar-item-button .fi-sidebar-item-icon {
        color: #FDBA74 !important;
        font-weight: 700 !important;
    }

    .fi-topbar > nav {
        background-color: var(--tk-surface) !important;
        border-bottom: 1px solid var(--tk-border) !important;
        box-shadow: none !important;
    }

    .fi-topbar-item-active > a,
    .fi-topbar-item.fi-active > a {
        background-color: var(--tk-accent-soft) !important;
        border: 1px solid var(--tk-accent) !important;
        border-radius: 0.6rem !important;
    }

    .fi-topbar-item-active > a span,
    .fi-topbar-item-active > a svg,
    .fi-topbar-item.fi-active > a span,
    .fi-topbar-item.fi-active > a svg {
        color: var(--tk-accent-soft-fg) !important;
        font-weight: 700 !important;
    }

    .fi-ta-ctn,
    .fi-section,
    .fi-wi-stats-overview-stat {
        border-radius: 0.9rem !important;
        border: 1px solid var(--tk-border) !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
    }

    .fi-ta-row {
        transition: background-color 120ms ease !important;
    }

    /* Brand lockup */
    .tk-brand-wrap {
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
    }

    .tk-brand-mark {
        width: 2.2rem;
        height: 2.2rem;
        border-radius: 0.65rem;
        background-color: var(--tk-accent);
        color: var(--tk-accent-fg);
        font-weight: 700;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .tk-brand-text {
        display: flex;
        flex-direction: column;
        line-height: 1.15;
    }

    .tk-brand-name {
        font-size: 0.98rem;
        font-weight: 700;
        color: var(--tk-ink);
        letter-spacing: -0.01em;
    }

    .tk-brand-role {
        font-size: 0.72rem;
        font-weight: 500;
        color: var(--tk-muted);
    }

    /* Topbar shortcuts */
    .tk-topbar-actions {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-inline-end: 0.5rem;
    }

    .tk-topbar-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        min-height: 2.35rem;
        padding: 0 0.75rem;
        border-radius: 0.55rem;
        font-size: 0.8rem;
        font-weight: 600;
        border: 1px solid var(--tk-border-strong);
        background-color: var(--tk-surface);
        color: var(--tk-ink);
        text-decoration: none;
        transition: background-color 140ms ease;
    }

    .tk-topbar-btn:hover {
        background-color: var(--tk-subtle);
    }

    .tk-topbar-btn-accent {
        background-color: var(--tk-accent-soft);
        color: var(--tk-accent-soft-fg);
        border-color: var(--tk-accent);
    }

    .tk-ico-sm {
        width: 1rem;
        height: 1rem;
        flex-shrink: 0;
    }

    @media (max-width: 639px) {
        .tk-hide-xs {
            display: none;
        }
    }

    /* Custom interactive workspace layout */
    .tk-stack {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .tk-panel {
        background-color: var(--tk-surface);
        border: 1px solid var(--tk-border);
        border-radius: 0.9rem;
        padding: 1.25rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .tk-panel-subtle {
        background-color: var(--tk-subtle);
        border: 1px solid var(--tk-border);
        border-radius: 0.75rem;
        padding: 0.9rem 1rem;
    }

    .tk-hero-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .tk-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--tk-ink);
        line-height: 1.25;
        margin: 0;
    }

    .tk-subtitle {
        font-size: 0.875rem;
        color: var(--tk-muted);
        margin: 0.25rem 0 0 0;
    }

    .tk-section-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--tk-ink);
        margin: 0;
    }

    .tk-btn-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }

    .tk-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        min-height: 2.75rem;
        padding: 0 0.95rem;
        border-radius: 0.6rem;
        font-size: 0.84rem;
        font-weight: 600;
        border: 1px solid var(--tk-border-strong);
        background-color: var(--tk-surface);
        color: var(--tk-ink);
        cursor: pointer;
        text-decoration: none;
        transition: background-color 140ms ease, border-color 140ms ease;
    }

    .tk-btn:hover {
        background-color: var(--tk-subtle);
    }

    .tk-btn-sm {
        min-height: 2.35rem;
        padding: 0 0.72rem;
        font-size: 0.78rem;
    }

    .tk-btn-primary {
        background-color: var(--tk-accent);
        color: var(--tk-accent-fg);
        border-color: transparent;
    }

    .tk-btn-primary:hover {
        background-color: var(--tk-accent-hover);
    }

    .tk-btn-soft {
        background-color: var(--tk-accent-soft);
        color: var(--tk-accent-soft-fg);
        border-color: var(--tk-accent);
    }

    .tk-btn-ok {
        background-color: var(--tk-ok-bg);
        color: var(--tk-ok-fg);
        border-color: var(--tk-ok-bd);
    }

    .tk-input {
        width: 100%;
        min-height: 2.75rem;
        padding: 0.5rem 0.8rem;
        border-radius: 0.6rem;
        border: 1px solid var(--tk-border-strong);
        background-color: var(--tk-surface);
        color: var(--tk-ink);
        font-size: 0.875rem;
    }

    .tk-textarea {
        width: 100%;
        padding: 0.65rem 0.8rem;
        border-radius: 0.6rem;
        border: 1px solid var(--tk-border-strong);
        background-color: var(--tk-surface);
        color: var(--tk-ink);
        font-size: 0.875rem;
        line-height: 1.5;
    }

    .tk-btn:focus-visible,
    .tk-input:focus-visible,
    .tk-textarea:focus-visible,
    .tk-topbar-btn:focus-visible {
        outline: 3px solid var(--tk-accent);
        outline-offset: 2px;
    }

    /* Metric strip (2x2 compact grid even on phones, 4-col on desktop!) */
    .tk-metrics-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.65rem;
    }

    @media (max-width: 359px) {
        .tk-metrics-grid {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
    }

    @media (min-width: 640px) {
        .tk-metrics-grid {
            gap: 0.85rem;
        }
    }

    @media (min-width: 1200px) {
        .tk-metrics-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    .tk-metric-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 0.4rem;
        background-color: var(--tk-surface);
        border: 1px solid var(--tk-border);
        border-radius: 0.85rem;
        padding: 1rem 1.15rem;
        text-decoration: none;
        transition: border-color 140ms ease;
    }

    .tk-metric-card:hover {
        border-color: var(--tk-accent);
    }

    .tk-metric-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--tk-muted);
    }

    .tk-metric-value {
        font-size: 1.45rem;
        font-weight: 700;
        color: var(--tk-ink);
        font-variant-numeric: tabular-nums;
        line-height: 1.2;
    }

    .tk-metric-note {
        font-size: 0.76rem;
        color: var(--tk-muted);
    }

    /* Admin 2-column split */
    .tk-admin-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1.25rem;
        align-items: start;
    }

    @media (min-width: 1120px) {
        .tk-admin-grid {
            grid-template-columns: minmax(0, 7fr) minmax(0, 5fr);
        }
    }

    /* Workshop 4-column interactive board */
    .tk-board-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1rem;
        align-items: start;
    }

    @media (min-width: 768px) {
        .tk-board-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1360px) {
        .tk-board-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    .tk-lane {
        background-color: var(--tk-subtle);
        border: 1px solid var(--tk-border);
        border-radius: 0.9rem;
        padding: 0.85rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        min-height: 14rem;
    }

    .tk-lane-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.15rem 0.2rem;
    }

    .tk-lane-title {
        font-size: 0.86rem;
        font-weight: 700;
        color: var(--tk-ink);
    }

    .tk-count-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.65rem;
        height: 1.55rem;
        padding: 0 0.45rem;
        border-radius: 0.45rem;
        font-size: 0.75rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        background-color: var(--tk-surface);
        color: var(--tk-ink);
        border: 1px solid var(--tk-border);
    }

    .tk-job-card {
        background-color: var(--tk-surface);
        border: 1px solid var(--tk-border);
        border-radius: 0.75rem;
        padding: 0.95rem;
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
        transition: border-color 140ms ease, box-shadow 140ms ease;
    }

    .tk-job-card:hover {
        border-color: var(--tk-accent);
        box-shadow: 0 4px 12px -4px rgba(15, 23, 42, 0.1);
    }

    .tk-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.18rem 0.52rem;
        border-radius: 0.4rem;
        font-size: 0.72rem;
        font-weight: 600;
        border: 1px solid transparent;
    }

    .tk-badge-blue {
        background-color: var(--tk-accent-soft);
        color: var(--tk-accent-soft-fg);
        border-color: var(--tk-accent);
    }

    .tk-badge-ok {
        background-color: var(--tk-ok-bg);
        color: var(--tk-ok-fg);
        border-color: var(--tk-ok-bd);
    }

    .tk-badge-warn {
        background-color: var(--tk-warn-bg);
        color: var(--tk-warn-fg);
        border-color: var(--tk-warn-bd);
    }

    .tk-badge-err {
        background-color: var(--tk-err-bg);
        color: var(--tk-err-fg);
        border-color: var(--tk-err-bd);
    }

    .tk-badge-muted {
        background-color: var(--tk-subtle);
        color: var(--tk-muted);
        border-color: var(--tk-border);
    }

    .tk-row-item {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.85rem;
        border-radius: 0.75rem;
        background-color: var(--tk-subtle);
        border: 1px solid var(--tk-border);
    }

    .tk-stepper {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .tk-step-btn {
        width: 2.35rem;
        height: 2.35rem;
        border-radius: 0.5rem;
        border: 1px solid var(--tk-border-strong);
        background-color: var(--tk-surface);
        color: var(--tk-ink);
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
    }

    .tk-step-btn:hover {
        background-color: var(--tk-accent-soft);
        color: var(--tk-accent-soft-fg);
    }

    .tk-step-val {
        min-width: 2.2rem;
        text-align: center;
        font-weight: 700;
        font-size: 0.9rem;
        font-variant-numeric: tabular-nums;
        color: var(--tk-ink);
    }

    /* Slide-over drawer modal */
    .tk-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 60;
        background-color: rgba(15, 23, 42, 0.58);
        display: flex;
        justify-content: flex-end;
    }

    .tk-drawer {
        width: 100%;
        max-width: 34rem;
        height: 100dvh;
        background-color: var(--tk-surface);
        border-inline-start: 1px solid var(--tk-border);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .tk-drawer-head {
        padding: 1.15rem 1.35rem;
        border-bottom: 1px solid var(--tk-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .tk-drawer-body {
        padding: 1.25rem 1.35rem;
        overflow-y: auto;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .tk-drawer-foot {
        padding: 1rem 1.35rem;
        border-top: 1px solid var(--tk-border);
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 0.6rem;
    }

    .tk-field-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 0.85rem;
    }

    @media (min-width: 480px) {
        .tk-field-grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .tk-label {
        display: block;
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--tk-ink);
        margin-bottom: 0.35rem;
    }

    .tk-mono {
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-variant-numeric: tabular-nums;
    }

    .tk-empty {
        padding: 1.75rem 1rem;
        text-align: center;
        border: 1px dashed var(--tk-border-strong);
        border-radius: 0.75rem;
        color: var(--tk-muted);
        font-size: 0.83rem;
    }

    /* Split-Screen Staff Auth Experience */
    .tk-auth-shell {
        min-height: 100dvh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background-color: var(--tk-canvas);
    }

    @media (min-width: 640px) {
        .tk-auth-shell {
            padding: 2rem;
        }
    }

    .tk-auth-card {
        width: 100%;
        max-width: 64rem;
        border-radius: 1.25rem;
        overflow: hidden;
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        background-color: var(--tk-surface);
        border: 1px solid var(--tk-border);
        box-shadow: 0 22px 48px -14px rgba(15, 23, 42, 0.22);
    }

    @media (min-width: 960px) {
        .tk-auth-card {
            grid-template-columns: minmax(0, 5fr) minmax(0, 7fr);
            min-height: 37rem;
        }
    }

    .tk-auth-showcase {
        background-color: #0F172A;
        color: #F8FAFC;
        padding: 1rem 1.25rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 1.25rem;
        order: 2;
        border-top: 1px solid #334155;
    }

    .tk-auth-form-pane {
        order: 1;
    }

    .tk-auth-desktop-only {
        display: none !important;
    }

    .tk-auth-mobile-only {
        display: inline-flex;
    }

    .tk-auth-switch-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.5rem;
    }

    @media (min-width: 960px) {
        .tk-auth-showcase {
            padding: 2.35rem;
            order: 1;
            border-top: 0;
            border-inline-end: 1px solid #334155;
        }

        .tk-auth-form-pane {
            order: 2;
        }

        .tk-auth-desktop-only {
            display: flex !important;
        }

        .tk-auth-mobile-only {
            display: none !important;
        }

        .tk-auth-switch-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
            padding-top: 0.5rem;
        }
    }

    .tk-auth-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.65rem;
        border-radius: 0.4rem;
        background-color: #FB923C;
        color: #0F172A;
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        font-weight: 800;
        font-size: 0.7rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .tk-auth-showcase-title {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        font-size: 1.45rem;
        font-weight: 800;
        color: #F8FAFC;
        line-height: 1.22;
        letter-spacing: -0.02em;
        margin: 0.65rem 0 0 0;
    }

    .tk-auth-showcase-desc {
        font-size: 0.84rem;
        color: #CBD5E1;
        line-height: 1.55;
        margin: 0.5rem 0 0 0;
    }

    .tk-auth-art {
        width: 100%;
        border-radius: 0.85rem;
        border: 1px solid #334155;
        background-color: #0B1220;
        overflow: hidden;
    }

    .tk-auth-feature-list {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .tk-auth-feature-item {
        background-color: #1E293B;
        border: 1px solid #334155;
        border-radius: 0.7rem;
        padding: 0.65rem 0.85rem;
        display: flex;
        align-items: flex-start;
        gap: 0.6rem;
        font-size: 0.8rem;
        color: #CBD5E1;
        line-height: 1.4;
    }

    .tk-auth-feature-dot {
        width: 0.45rem;
        height: 0.45rem;
        border-radius: 999px;
        background-color: #FB923C;
        margin-top: 0.35rem;
        flex-shrink: 0;
    }

    .tk-auth-ink-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        min-height: 2.75rem;
        padding: 0 0.85rem;
        border-radius: 0.6rem;
        font-size: 0.78rem;
        font-weight: 600;
        color: #F8FAFC;
        background-color: transparent;
        border: 1px solid #8FA0B8;
        text-decoration: none;
        transition: background-color 150ms ease, border-color 150ms ease;
    }

    .tk-auth-ink-btn:hover {
        background-color: rgba(255, 255, 255, 0.08);
        border-color: #FB923C;
    }

    .tk-auth-form-pane {
        padding: 1.65rem 1.35rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 1.5rem;
        background-color: var(--tk-surface);
        color: var(--tk-ink);
    }

    @media (min-width: 640px) {
        .tk-auth-form-pane {
            padding: 2.35rem 2.5rem;
        }
    }

    .tk-auth-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .tk-auth-heading {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        font-size: 1.55rem;
        font-weight: 800;
        color: var(--tk-ink);
        margin: 0;
        line-height: 1.2;
        letter-spacing: -0.02em;
    }

    .tk-auth-desc {
        font-size: 0.86rem;
        color: var(--tk-muted);
        margin: 0.35rem 0 0 0;
        line-height: 1.5;
    }

    .tk-auth-form {
        display: flex;
        flex-direction: column;
        gap: 1.05rem;
    }

    .tk-auth-label-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.38rem;
    }

    .tk-auth-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .tk-auth-input {
        width: 100%;
        min-height: 2.9rem;
        padding: 0.55rem 0.9rem 0.55rem 2.55rem;
        border-radius: 0.65rem;
        border: 1px solid var(--tk-border-strong);
        background-color: var(--tk-surface);
        color: var(--tk-ink);
        font-size: 0.9rem;
        transition: border-color 150ms ease;
    }

    .tk-auth-input-pw {
        padding-inline-end: 4.5rem;
    }

    .tk-auth-input:hover,
    .tk-auth-input:focus {
        border-color: var(--tk-accent);
    }

    .tk-auth-input-icon {
        position: absolute;
        inset-inline-start: 0.82rem;
        width: 1.1rem;
        height: 1.1rem;
        color: var(--tk-muted);
        pointer-events: none;
    }

    .tk-auth-pw-toggle {
        position: absolute;
        inset-inline-end: 0.35rem;
        min-height: 2.25rem;
        padding: 0 0.65rem;
        border-radius: 0.45rem;
        border: 1px solid transparent;
        background: transparent;
        color: var(--tk-muted);
        font-size: 0.76rem;
        font-weight: 600;
        cursor: pointer;
    }

    .tk-auth-pw-toggle:hover {
        color: var(--tk-ink);
        background-color: var(--tk-subtle);
    }

    .tk-auth-link {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--tk-accent-soft-fg);
        text-decoration: none;
        padding: 0.15rem 0;
    }

    .tk-auth-link:hover {
        text-decoration: underline;
    }

    .tk-auth-captcha-box {
        background-color: var(--tk-subtle);
        border: 1px solid var(--tk-border-strong);
        border-radius: 0.75rem;
        padding: 0.85rem 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
        overflow: hidden;
    }

    .tk-recaptcha-mount {
        min-height: 78px;
    }

    @media (max-width: 420px) {
        .tk-recaptcha-mount {
            transform: scale(0.85);
            transform-origin: left top;
            min-height: 66px;
        }
    }

    .tk-auth-captcha-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.65rem;
        flex-wrap: wrap;
    }

    .tk-auth-captcha-input {
        width: 6.5rem;
        min-height: 2.65rem;
        padding: 0.4rem 0.7rem;
        border-radius: 0.55rem;
        border: 1px solid var(--tk-border-strong);
        background-color: var(--tk-surface);
        color: var(--tk-ink);
        font-size: 0.92rem;
        font-weight: 700;
        text-align: center;
        font-variant-numeric: tabular-nums;
    }

    .tk-auth-submit {
        width: 100%;
        min-height: 3rem;
        padding: 0 1.25rem;
        border-radius: 0.7rem;
        border: 1px solid transparent;
        background-color: var(--tk-accent);
        color: var(--tk-accent-fg);
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        font-size: 0.95rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: background-color 150ms ease, transform 120ms ease;
    }

    .tk-auth-submit:hover {
        background-color: var(--tk-accent-hover);
    }

    .tk-auth-submit:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }

    .tk-auth-error {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--tk-err-fg);
        margin: 0.3rem 0 0 0;
    }

    .tk-auth-alert-ok {
        background-color: var(--tk-ok-bg);
        color: var(--tk-ok-fg);
        border: 1px solid var(--tk-ok-bd);
        border-radius: 0.75rem;
        padding: 0.9rem 1rem;
        font-size: 0.84rem;
        line-height: 1.5;
    }

    .tk-auth-alert-info {
        background-color: var(--tk-accent-soft);
        color: var(--tk-accent-soft-fg);
        border: 1px solid var(--tk-accent);
        border-radius: 0.75rem;
        padding: 0.8rem 0.95rem;
        font-size: 0.78rem;
        line-height: 1.45;
    }

    .tk-auth-input:focus-visible,
    .tk-auth-captcha-input:focus-visible,
    .tk-auth-pw-toggle:focus-visible,
    .tk-auth-submit:focus-visible,
    .tk-auth-ink-btn:focus-visible,
    .tk-auth-link:focus-visible {
        outline: 3px solid var(--tk-accent);
        outline-offset: 2px;
    }

    /* Zero-cost GPU micro-animations & clean command workspace */
    @keyframes tk-enter {
        from {
            opacity: 0;
            transform: translate3d(0, 8px, 0);
        }
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @keyframes tk-drawer-in {
        from {
            opacity: 0.4;
            transform: translate3d(100%, 0, 0);
        }
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @keyframes tk-backdrop-in {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes tk-pulse-ring {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.45; transform: scale(1.22); }
    }

    .tk-anim-enter {
        animation: tk-enter 220ms cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    .tk-delay-1 { animation-delay: 35ms; }
    .tk-delay-2 { animation-delay: 70ms; }
    .tk-delay-3 { animation-delay: 105ms; }
    .tk-delay-4 { animation-delay: 140ms; }

    .tk-modal-backdrop {
        animation: tk-backdrop-in 160ms ease-out both;
    }

    .tk-drawer {
        animation: tk-drawer-in 220ms cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    .tk-live-dot {
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 999px;
        background-color: #EF4444;
        display: inline-block;
        animation: tk-pulse-ring 1.8s ease-in-out infinite;
    }

    .tk-cmd-banner {
        background-color: #0F172A;
        color: #F8FAFC;
        border: 1px solid #334155;
        border-radius: 1rem;
        padding: 1.35rem 1.5rem;
        box-shadow: 0 12px 28px -12px rgba(15, 23, 42, 0.28);
    }

    html.dark .tk-cmd-banner {
        background-color: #1A2438;
        border-color: #334155;
    }

    .tk-cmd-title {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        font-size: 1.4rem;
        font-weight: 800;
        color: #F8FAFC;
        letter-spacing: -0.02em;
        margin: 0;
        line-height: 1.22;
    }

    .tk-cmd-sub {
        font-size: 0.85rem;
        color: #CBD5E1;
        margin: 0.28rem 0 0 0;
        line-height: 1.45;
    }

    .tk-cmd-btn-accent {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        min-height: 2.75rem;
        padding: 0 1rem;
        border-radius: 0.65rem;
        font-size: 0.83rem;
        font-weight: 700;
        background-color: #FB923C;
        color: #0F172A;
        border: 1px solid transparent;
        text-decoration: none;
        cursor: pointer;
        transition: background-color 140ms ease, transform 110ms ease;
    }

    .tk-cmd-btn-accent:hover {
        background-color: #FDBA74;
        transform: translateY(-1px);
    }

    .tk-cmd-btn-outline {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        min-height: 2.75rem;
        padding: 0 0.95rem;
        border-radius: 0.65rem;
        font-size: 0.82rem;
        font-weight: 600;
        background-color: rgba(255, 255, 255, 0.04);
        color: #F8FAFC;
        border: 1px solid #8FA0B8;
        text-decoration: none;
        cursor: pointer;
        transition: background-color 140ms ease, border-color 140ms ease;
    }

    .tk-cmd-btn-outline:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: #FB923C;
    }

    .tk-cmd-search {
        width: 100%;
        min-height: 2.75rem;
        padding: 0.5rem 0.85rem 0.5rem 2.4rem;
        border-radius: 0.65rem;
        background-color: #0B1220;
        color: #F8FAFC;
        border: 1px solid #8FA0B8;
        font-size: 0.85rem;
        transition: border-color 140ms ease;
    }

    .tk-cmd-search::placeholder {
        color: #CBD5E1;
        opacity: 0.85;
    }

    .tk-cmd-search:focus {
        border-color: #FB923C;
        outline: 2px solid #FB923C;
        outline-offset: 1px;
    }

    .tk-kpi-card {
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 0.55rem;
        background-color: var(--tk-surface);
        border: 1px solid var(--tk-border);
        border-radius: 0.95rem;
        padding: 1.1rem 1.2rem;
        text-decoration: none;
        transition: transform 170ms cubic-bezier(0.16, 1, 0.3, 1), box-shadow 170ms ease, border-color 170ms ease;
    }

    .tk-kpi-card:hover {
        transform: translateY(-3px);
        border-color: var(--tk-accent);
        box-shadow: 0 12px 24px -10px rgba(15, 23, 42, 0.14);
    }

    .tk-kpi-bar {
        position: absolute;
        inset-inline: 0;
        top: 0;
        height: 3px;
    }

    .tk-kpi-value {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--tk-ink);
        font-variant-numeric: tabular-nums;
        letter-spacing: -0.02em;
        line-height: 1.15;
    }

    .tk-seg-tabs {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        max-width: 100%;
        gap: 0.35rem;
        padding: 0.32rem;
        border-radius: 0.8rem;
        background-color: var(--tk-subtle);
        border: 1px solid var(--tk-border);
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .tk-seg-tabs::-webkit-scrollbar {
        display: none;
    }

    .tk-seg-btn {
        display: inline-flex;
        align-items: center;
        flex-shrink: 0;
        white-space: nowrap;
        gap: 0.45rem;
        min-height: 2.55rem;
        padding: 0 0.9rem;
        border-radius: 0.6rem;
        border: 1px solid transparent;
        background: transparent;
        color: var(--tk-muted);
        font-size: 0.81rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 140ms ease, color 140ms ease, box-shadow 140ms ease;
    }

    .tk-seg-btn:hover {
        color: var(--tk-ink);
    }

    .tk-seg-btn-active {
        background-color: var(--tk-surface);
        color: var(--tk-ink);
        border-color: var(--tk-border-strong);
        font-weight: 700;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
    }

    .tk-clean-table {
        display: flex;
        flex-direction: column;
        border: 1px solid var(--tk-border);
        border-radius: 0.8rem;
        overflow: hidden;
        background-color: var(--tk-surface);
    }

    .tk-clean-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.85rem;
        padding: 0.95rem 1.15rem;
        border-bottom: 1px solid var(--tk-border);
        transition: background-color 130ms ease;
    }

    .tk-clean-row:last-child {
        border-bottom: 0;
    }

    .tk-clean-row:hover {
        background-color: var(--tk-subtle);
    }

    .tk-lane-card {
        background-color: var(--tk-surface);
        border: 1px solid var(--tk-border);
        border-radius: 0.8rem;
        padding: 0.95rem;
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        transition: transform 160ms cubic-bezier(0.16, 1, 0.3, 1), border-color 160ms ease, box-shadow 160ms ease;
    }

    .tk-lane-card:hover {
        transform: translateY(-2px);
        border-color: var(--tk-accent);
        box-shadow: 0 10px 20px -8px rgba(15, 23, 42, 0.14);
    }

    .tk-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .tk-cmd-actions-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.5rem;
        width: 100%;
    }

    .tk-cmd-span-mobile {
        grid-column: span 2 / span 2;
    }

    @media (min-width: 640px) {
        .tk-cmd-actions-grid {
            display: flex;
            flex-wrap: wrap;
            width: auto;
        }
        .tk-cmd-span-mobile {
            grid-column: auto;
        }
    }

    .tk-scroll-pills {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        max-width: 100%;
        gap: 0.45rem;
        scrollbar-width: none;
    }

    .tk-scroll-pills::-webkit-scrollbar {
        display: none;
    }

    .tk-mobile-lane-bar {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        max-width: 100%;
        gap: 0.35rem;
        padding: 0.35rem;
        border-radius: 0.85rem;
        background-color: var(--tk-subtle);
        border: 1px solid var(--tk-border);
        scrollbar-width: none;
    }

    .tk-mobile-lane-bar::-webkit-scrollbar {
        display: none;
    }

    .tk-hide-lane-mobile {
        display: none !important;
    }

    @media (min-width: 768px) {
        .tk-mobile-lane-bar {
            display: none !important;
        }
        .tk-hide-lane-mobile {
            display: flex !important;
        }
    }

    .tk-clean-row-price {
        flex: 0 0 auto;
        text-align: right;
    }

    .tk-clean-row-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }

    @media (max-width: 639px) {
        .fi-main {
            padding-inline: 0.85rem !important;
        }
        .tk-topbar-btn {
            padding: 0 0.55rem;
            min-height: 2.25rem;
        }
        .tk-cmd-banner {
            padding: 1rem 1.05rem;
        }
        .tk-cmd-title {
            font-size: 1.18rem;
        }
        .tk-cmd-sub {
            font-size: 0.78rem;
        }
        .tk-kpi-card {
            padding: 0.85rem 0.9rem;
        }
        .tk-kpi-value {
            font-size: 1.25rem;
        }
        .tk-panel {
            padding: 0.95rem;
        }
        .tk-clean-row {
            flex-direction: column;
            align-items: stretch;
            gap: 0.65rem;
            padding: 0.85rem 0.9rem;
        }
        .tk-clean-row-price {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            text-align: left;
            padding-top: 0.45rem;
            border-top: 1px dashed var(--tk-border);
        }
        .tk-clean-row-actions {
            width: 100%;
        }
        .tk-clean-row-actions > .tk-btn {
            flex: 1 1 auto;
        }
        .fi-ta-content {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
    }

    .tk-btn:active,
    .tk-cmd-btn-accent:active,
    .tk-cmd-btn-outline:active,
    .tk-step-btn:active {
        transform: scale(0.97);
    }

    @media (prefers-reduced-motion: reduce) {
        .tk-anim-enter,
        .tk-modal-backdrop,
        .tk-drawer,
        .tk-live-dot {
            animation: none !important;
        }
        .tk-kpi-card,
        .tk-lane-card,
        .tk-btn,
        .tk-cmd-btn-accent {
            transition: none !important;
            transform: none !important;
        }
    }
</style>
