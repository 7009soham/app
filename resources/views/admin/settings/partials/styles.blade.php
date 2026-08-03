{{-- Shared settings chrome. Each settings page pushes this once. --}}
<style>
    .settings-layout {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 24px;
    }

    .settings-nav {
        position: sticky;
        top: 84px;
        height: fit-content;
    }

    .settings-menu {
        list-style: none;
    }

    .settings-menu li {
        border-radius: 6px;
        transition: all 0.2s;
    }

    .settings-menu li a {
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--color-gray-600);
        text-decoration: none;
        border-radius: 6px;
    }

    .settings-menu li:hover {
        background: var(--color-gray-100);
    }

    .settings-menu li.active {
        background: var(--admin-primary);
    }

    .settings-menu li.active a {
        color: white;
    }

    @media (max-width: 768px) {
        .settings-layout {
            grid-template-columns: 1fr;
        }

        .settings-nav {
            position: static;
        }
    }
</style>
