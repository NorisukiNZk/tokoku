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

    /* Refined Filament shell matching Tokoku Slate + Marketplace Blue design */
    .fi-body {
        background-color: var(--tk-canvas) !important;
    }

    .fi-sidebar {
        background-color: var(--tk-surface) !important;
        border-inline-end: 1px solid var(--tk-border) !important;
    }

    .fi-topbar > nav {
        background-color: var(--tk-surface) !important;
        border-bottom: 1px solid var(--tk-border) !important;
        box-shadow: none !important;
    }

    .fi-sidebar-item-active > .fi-sidebar-item-button {
        background-color: var(--tk-accent-soft) !important;
        border-radius: 0.65rem !important;
    }

    .fi-sidebar-item-active > .fi-sidebar-item-button .fi-sidebar-item-label,
    .fi-sidebar-item-active > .fi-sidebar-item-button .fi-sidebar-item-icon {
        color: var(--tk-accent-soft-fg) !important;
        font-weight: 600 !important;
    }

    .fi-ta-ctn,
    .fi-section,
    .fi-wi-stats-overview-stat {
        border-radius: 0.85rem !important;
        border: 1px solid var(--tk-border) !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
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

    /* Metric strip */
    .tk-metrics-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 0.85rem;
    }

    @media (min-width: 640px) {
        .tk-metrics-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
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
        padding: 1.75rem 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 1.5rem;
        border-bottom: 1px solid #334155;
    }

    @media (min-width: 960px) {
        .tk-auth-showcase {
            padding: 2.35rem;
            border-bottom: 0;
            border-inline-end: 1px solid #334155;
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
</style>
