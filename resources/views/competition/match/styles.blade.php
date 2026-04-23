<style>
    .match-layout {
        align-items: flex-start;
    }

    .match-sidebar-card,
    .match-players-card {
        border-radius: 14px;
        overflow: hidden;
    }

    .match-sticky-card {
        position: sticky;
        top: 90px;
    }

    .match-card-body {
        padding: 1.25rem;
    }

    .match-form-block {
        margin-bottom: 1rem;
        padding: 1rem;
        border: 1px solid rgba(120, 130, 140, 0.18);
        border-radius: 12px;
        background-color: transparent;
    }

    .match-form-block:last-child {
        margin-bottom: 0;
    }

    .match-form-heading {
        margin-bottom: 0.85rem;
    }

    .match-form-title {
        margin-bottom: 0.2rem;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .match-form-subtitle {
        margin-bottom: 0;
        font-size: 0.75rem;
        color: #99abb4;
    }

    .match-form-block .form-group {
        margin-bottom: 0.8rem;
    }

    .match-form-block .form-group:last-child {
        margin-bottom: 0;
    }

    .match-form-block label {
        margin-bottom: 0.35rem;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .match-score-strip {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .match-score-side {
        flex: 1 1 0;
    }

    .match-score-divider {
        flex: 0 0 auto;
        padding-top: 1.95rem;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #99abb4;
    }

    .match-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: flex-end;
    }

    .match-toolbar-item {
        flex: 1 1 160px;
    }

    .match-toolbar-item--file {
        flex: 1 1 220px;
    }

    .match-toolbar .btn {
        width: 100%;
    }

    .match-toolbar-note {
        margin-top: 0.75rem;
        margin-bottom: 0;
        font-size: 0.75rem;
        color: #99abb4;
        line-height: 1.4;
    }

    .match-save-bar {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(120, 130, 140, 0.18);
    }

    .match-save-bar .btn {
        width: 100%;
    }

    .match-table-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .match-table-title {
        margin-bottom: 0.2rem;
        font-size: 1rem;
        font-weight: 600;
    }

    .match-table-count {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background-color: rgba(120, 130, 140, 0.12);
        font-size: 0.78rem;
        font-weight: 600;
    }

    .match-table-wrapper {
        border: 1px solid rgba(120, 130, 140, 0.18);
        border-radius: 12px;
        overflow: hidden;
    }

    .match-table {
        min-width: 1120px;
        margin-bottom: 0;
    }

    .match-table thead th {
        border-top: 0;
        white-space: nowrap;
        font-size: 0.73rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        background-color: rgba(120, 130, 140, 0.08);
    }

    .match-table td {
        padding: 0.45rem 0.35rem;
        vertical-align: middle;
    }

    .match-player-cell {
        min-width: 250px;
    }

    .match-player-meta {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        min-width: 230px;
    }

    .match-player-avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        object-fit: cover;
        flex-shrink: 0;
    }

    .match-player-name {
        display: block;
        font-weight: 600;
        line-height: 1.25;
    }

    .match-player-code {
        display: inline-flex;
        align-items: center;
        margin-top: 0.25rem;
        padding: 0.15rem 0.5rem;
        border: 1px solid rgba(120, 130, 140, 0.22);
        border-radius: 999px;
        font-size: 0.72rem;
        line-height: 1.2;
    }

    .match-player-contact {
        display: block;
        margin-top: 0.25rem;
        font-size: 0.72rem;
        line-height: 1.3;
    }

    .match-metric-cell {
        min-width: 82px;
    }

    .match-position-cell {
        min-width: 120px;
    }

    .match-observation-cell {
        min-width: 185px;
    }

    .match-observation-field {
        min-width: 180px;
        resize: vertical;
    }

    .match-table .form-control-sm,
    .match-table .select {
        min-width: 72px;
    }

    @media (max-width: 1199.98px) {
        .match-sticky-card {
            position: static;
        }
    }

    @media (max-width: 575.98px) {
        .match-card-body {
            padding: 1rem;
        }

        .match-toolbar-item,
        .match-toolbar-item--file {
            flex-basis: 100%;
        }

        .match-score-strip {
            flex-wrap: wrap;
        }

        .match-score-divider {
            width: 100%;
            padding-top: 0;
            text-align: center;
        }
    }
</style>
