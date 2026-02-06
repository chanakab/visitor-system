<!-- Core Styles -->
<link rel="stylesheet" href="style.css">

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">

<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<!-- jQuery & DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<!-- DataTables Export Buttons (Print, CSV, Excel) -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<style>
    /* DataTable Overrides for Glassmorphism */
    .dataTables_wrapper {
        padding: 20px;
        background: rgba(255, 255, 255, 0.5);
        border-radius: 12px;
        margin-top: 20px;
    }
    table.dataTable thead th {
        background: rgba(255, 255, 255, 0.8);
        border-radius: 8px;
        padding: 12px;
    }
    table.dataTable tbody tr {
        background: transparent !important;
        transition: background 0.2s;
    }
    table.dataTable tbody tr:hover {
        background: rgba(255, 255, 255, 0.6) !important;
    }
    button.dt-button {
        background: var(--primary) !important;
        color: white !important;
        border: none !important;
        border-radius: 6px !important;
        padding: 8px 16px !important;
        transition: opacity 0.2s;
    }
    button.dt-button:hover {
        opacity: 0.9;
    }
    .dataTables_filter input {
        border: 2px solid var(--border-light);
        border-radius: 6px;
        padding: 6px;
    }
</style>

<script>
    // Auto Initialize DataTables
    $(document).ready(function() {
        $('table').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            pageLength: 10,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search table..."
            }
        });
        lucide.createIcons();
    });
</script>
