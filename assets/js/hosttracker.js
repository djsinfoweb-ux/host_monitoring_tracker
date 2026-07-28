/**
 * Interações do módulo Host Monitoring Tracker.
 *
 * - Abre/fecha grupos filhos em qualquer nível.
 * - Mantém o estado recolhido no navegador.
 * - Funciona tanto na visualização quanto no gerenciamento de limites.
 */
(function () {
    'use strict';

    const STORAGE_PREFIX = 'hosttracker.collapsed.';

    function initializeHierarchyTable(table, tableIndex) {
        const rows = Array.from(table.querySelectorAll('tbody tr.hosttracker-row'));

        if (rows.length === 0) {
            return;
        }

        const rowsById = new Map();
        rows.forEach((row) => {
            rowsById.set(row.dataset.groupId, row);
        });

        const storageKey = STORAGE_PREFIX + (table.id || String(tableIndex));
        const collapsedIds = loadCollapsedIds(storageKey);

        rows.forEach((row) => {
            const toggle = row.querySelector('.hosttracker-toggle');
            if (!toggle) {
                return;
            }

            const isExpanded = !collapsedIds.has(row.dataset.groupId);
            setToggleState(toggle, isExpanded);
        });

        refreshVisibility(rows, rowsById);

        table.addEventListener('click', (event) => {
            const toggle = event.target.closest('.hosttracker-toggle');

            if (!toggle || !table.contains(toggle)) {
                return;
            }

            const row = toggle.closest('tr.hosttracker-row');
            if (!row) {
                return;
            }

            const nextState = toggle.getAttribute('aria-expanded') !== 'true';
            setToggleState(toggle, nextState);
            refreshVisibility(rows, rowsById);
            saveCollapsedIds(storageKey, rows);
        });
    }

    function setToggleState(toggle, expanded) {
        toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        toggle.setAttribute(
            'title',
            expanded ? 'Recolher subgrupos' : 'Expandir subgrupos'
        );
    }

    function refreshVisibility(rows, rowsById) {
        rows.forEach((row) => {
            let parentId = row.dataset.parentId || '';
            let visible = true;
            const visited = new Set();

            while (parentId !== '') {
                if (visited.has(parentId)) {
                    // Proteção contra uma eventual relação circular inválida.
                    break;
                }
                visited.add(parentId);

                const parentRow = rowsById.get(parentId);
                if (!parentRow) {
                    break;
                }

                const parentToggle = parentRow.querySelector('.hosttracker-toggle');
                if (parentToggle && parentToggle.getAttribute('aria-expanded') !== 'true') {
                    visible = false;
                    break;
                }

                parentId = parentRow.dataset.parentId || '';
            }

            row.hidden = !visible;
        });
    }

    function loadCollapsedIds(storageKey) {
        try {
            const stored = window.localStorage.getItem(storageKey);
            const values = stored ? JSON.parse(stored) : [];

            if (!Array.isArray(values)) {
                return new Set();
            }

            return new Set(values.map(String));
        }
        catch (error) {
            return new Set();
        }
    }

    function saveCollapsedIds(storageKey, rows) {
        const collapsed = [];

        rows.forEach((row) => {
            const toggle = row.querySelector('.hosttracker-toggle');
            if (toggle && toggle.getAttribute('aria-expanded') !== 'true') {
                collapsed.push(String(row.dataset.groupId));
            }
        });

        try {
            window.localStorage.setItem(storageKey, JSON.stringify(collapsed));
        }
        catch (error) {
            // O módulo continua funcionando mesmo se o navegador bloquear storage.
        }
    }

    function initializeStatusTooltips() {
        document.querySelectorAll('.status-ok').forEach((element) => {
            if (!element.hasAttribute('title')) {
                element.setAttribute(
                    'title',
                    'Quantidade de hosts dentro do limite contratado'
                );
            }
        });

        document.querySelectorAll('.status-overlimit').forEach((element) => {
            if (!element.hasAttribute('title')) {
                element.setAttribute(
                    'title',
                    'Quantidade de hosts acima do limite contratado'
                );
            }
        });
    }

    function initialize() {
        document.querySelectorAll('table.hosttracker-hierarchy-table')
            .forEach(initializeHierarchyTable);

        initializeStatusTooltips();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    }
    else {
        initialize();
    }
})();
