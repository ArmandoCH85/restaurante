<style>
    html.dark {
        color-scheme: dark;
    }

    .fi-sidebar {
        --sidebar-bg: #ffffff;
        --sidebar-surface: #f8fafc;
        --sidebar-border: #e2e8f0;
        --sidebar-text: #0f172a;
        --sidebar-muted: #475569;
        --sidebar-hover: #eef2ff;
        --sidebar-active-bg: #e0e7ff;
        --sidebar-active-text: #3730a3;
        --sidebar-active-border: rgba(55, 48, 163, 0.22);
        --sidebar-focus-ring: rgba(79, 70, 229, 0.35);
        --sidebar-group-text: #334155;
        --sidebar-group-bg: #f8fafc;
        --sidebar-scrollbar: rgba(71, 85, 105, 0.35);
        --sidebar-scrollbar-hover: rgba(51, 65, 85, 0.5);
        --sidebar-icon: #64748b;
        --sidebar-icon-active: #3730a3;
        --sidebar-icon-hover: #334155;
        --sidebar-badge-bg: #e0e7ff;
        --sidebar-badge-text: #312e81;
        --sidebar-badge-border: #c7d2fe;

        background: var(--sidebar-bg);
        border-inline-end: 1px solid var(--sidebar-border);
    }

    .dark .fi-sidebar {
        --sidebar-bg: #0f172a;
        --sidebar-surface: #111c33;
        --sidebar-border: #1e293b;
        --sidebar-text: #e2e8f0;
        --sidebar-muted: #94a3b8;
        --sidebar-hover: #1e293b;
        --sidebar-active-bg: #1e1b4b;
        --sidebar-active-text: #c7d2fe;
        --sidebar-active-border: rgba(129, 140, 248, 0.32);
        --sidebar-focus-ring: rgba(129, 140, 248, 0.45);
        --sidebar-group-text: #cbd5e1;
        --sidebar-group-bg: #111c33;
        --sidebar-scrollbar: rgba(148, 163, 184, 0.45);
        --sidebar-scrollbar-hover: rgba(148, 163, 184, 0.62);
        --sidebar-icon: #94a3b8;
        --sidebar-icon-active: #c7d2fe;
        --sidebar-icon-hover: #e2e8f0;
        --sidebar-badge-bg: #312e81;
        --sidebar-badge-text: #e0e7ff;
        --sidebar-badge-border: #4338ca;
    }

    .fi-sidebar-header {
        border-bottom: 1px solid var(--sidebar-border);
        padding-block: 0.75rem;
    }

    .fi-sidebar-header .fi-logo {
        margin-inline: auto;
    }

    .fi-sidebar-nav {
        background: var(--sidebar-bg);
        display: grid;
        gap: 0.25rem;
        padding: 1rem 0.875rem;
        scrollbar-color: var(--sidebar-scrollbar) transparent;
    }

    .fi-sidebar-group {
        margin-bottom: 0.45rem;
    }

    .fi-sidebar-group-label {
        align-items: center;
        background: var(--sidebar-group-bg);
        border-radius: 0.55rem;
        color: var(--sidebar-group-text);
        display: inline-flex;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        line-height: 1;
        margin-bottom: 0.35rem;
        padding: 0.35rem 0.5rem;
        text-transform: uppercase;
    }

    .fi-sidebar-item-group-button {
        border: 1px solid transparent;
        border-radius: 0.65rem;
        color: var(--sidebar-text);
        min-height: 2.75rem;
        -webkit-tap-highlight-color: transparent;
        touch-action: manipulation;
        cursor: pointer;
        transition: background-color 160ms ease, border-color 160ms ease, color 160ms ease, box-shadow 160ms ease;
    }

    .fi-sidebar-item-group-button:hover {
        background: var(--sidebar-hover);
        border-color: var(--sidebar-active-border);
    }

    .fi-sidebar-item-button {
        border: 1px solid transparent;
        border-radius: 0.7rem;
        color: var(--sidebar-text);
        font-size: 0.92rem;
        font-weight: 500;
        min-height: 2.75rem;
        -webkit-tap-highlight-color: transparent;
        touch-action: manipulation;
        cursor: pointer;
        transition: background-color 160ms ease, color 160ms ease, border-color 160ms ease, box-shadow 160ms ease;
    }

    .fi-sidebar-item-button:hover {
        background: var(--sidebar-hover);
        border-color: var(--sidebar-active-border);
        color: var(--sidebar-text);
    }

    .fi-sidebar-item-icon {
        color: var(--sidebar-icon);
        height: 1.1rem;
        transition: color 160ms ease;
        width: 1.1rem;
    }

    .fi-sidebar-item-button:hover .fi-sidebar-item-icon,
    .fi-sidebar-item-group-button:hover .fi-sidebar-item-icon {
        color: var(--sidebar-icon-hover);
    }

    .fi-sidebar-item-label {
        line-height: 1.3;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .fi-sidebar-item.fi-active > .fi-sidebar-item-button {
        background: var(--sidebar-active-bg);
        border-color: var(--sidebar-active-border);
        color: var(--sidebar-active-text);
        font-weight: 600;
        box-shadow: inset 3px 0 0 var(--sidebar-active-text);
    }

    .fi-sidebar-item.fi-active .fi-sidebar-item-icon,
    .fi-sidebar-item.fi-active .fi-sidebar-item-label {
        color: var(--sidebar-icon-active);
    }

    .fi-sidebar-item-button:focus-visible,
    .fi-sidebar-item-group-button:focus-visible {
        border-color: transparent;
        box-shadow: 0 0 0 2px var(--sidebar-focus-ring);
        outline: none;
    }

    .fi-sidebar-item:focus-within {
        border-radius: 0.7rem;
        box-shadow: 0 0 0 2px var(--sidebar-focus-ring);
    }

    .fi-sidebar .fi-badge,
    .fi-sidebar .fi-sidebar-item-badge {
        background: var(--sidebar-badge-bg);
        border: 1px solid var(--sidebar-badge-border);
        border-radius: 9999px;
        color: var(--sidebar-badge-text);
        font-variant-numeric: tabular-nums;
        font-weight: 600;
        min-width: 1.6rem;
        padding-inline: 0.4rem;
        text-align: center;
    }

    @media (prefers-reduced-motion: reduce) {
        .fi-sidebar-item-button,
        .fi-sidebar-item-group-button,
        .fi-sidebar-item-icon {
            transition: none;
        }
    }

    .fi-sidebar-nav::-webkit-scrollbar {
        width: 8px;
    }

    .fi-sidebar-nav::-webkit-scrollbar-thumb {
        background: var(--sidebar-scrollbar);
        border-radius: 9999px;
    }

    .fi-sidebar-nav::-webkit-scrollbar-thumb:hover {
        background: var(--sidebar-scrollbar-hover);
    }

    @media (max-width: 1024px) {
        .fi-sidebar-nav {
            padding: 0.85rem 0.75rem;
        }

        .fi-sidebar-item-button,
        .fi-sidebar-item-group-button {
            min-height: 2.9rem;
        }
    }
</style>
