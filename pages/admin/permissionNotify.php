<section>
    <div class="border-bottom pb-3 mb-4">
        <h4 class="mb-1 fw-semibold">Permission Requests</h4>
        <p class="text-muted small mb-0">
            Review and approve student permission requests
        </p>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle permission-table">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Permission Period</th>
                    <th>Reason</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody id="pendinPermission">
                <!-- rows here -->
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <nav class="mt-3">
        <ul id="pagination" class="pagination pagination-sm justify-content-center"></ul>
    </nav>
</section>



<script>
$(document).ready(function () {

    let allData = [];
    let currentPage = 1;
    const perPage = 8;

    /* ===============================
       FETCH DATA
    =============================== */
    function fetchPermissions() {

        $('#pendinPermission').html(`
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                </td>
            </tr>
        `);

        $.getJSON('api.php', { endpoint: 'fetch_permissions_admin' }, function (res) {

            if (!res.status || res.data.length === 0) {
                $('#pendinPermission').html(`
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No permission requests found
                        </td>
                    </tr>
                `);
                $('#pagination').empty();
                return;
            }

            allData = res.data;
            currentPage = 1;
            renderPage();
        });
    }

    /* ===============================
       RENDER PAGE
    =============================== */
    function renderPage() {

        const start = (currentPage - 1) * perPage;
        const pageData = allData.slice(start, start + perPage);

        let html = '';

        pageData.forEach(p => {
            html += `
            <tr>
                <!-- Student -->
                <td>
                    <div class="fw-semibold">${p.student_name}</div>
                </td>

                <!-- Course -->
                <td>
                    <div class="fw-semibold text-primary">${p.course}</div>
                    <div class="small text-muted">
                        ${p.term ?? '-'} • ${p.time ?? '-'}
                    </div>
                </td>

                <!-- Period -->
                <td>
                    <span class="date-pill">
                        <i class="bi bi-calendar3 me-1"></i>
                        ${p.start_date} → ${p.end_date}
                    </span>
                </td>

                <!-- Reason -->
                <td class="text-muted small">
                    ${p.reason}
                </td>

                <!-- Status -->
                <td class="text-center">
                    <span class="status-pill ${
                        p.status === 'pending' ? 'pending' : 'approved'
                    }">
                        ${p.status.toUpperCase()}
                    </span>
                </td>

                <!-- Action -->
                <td class="text-end">
                    ${
                        p.status === 'pending'
                        ? `<button class="btn btn-success btn-sm btnApprove"
                                data-id="${p.permission_id}">
                                Approve
                           </button>`
                        : `<i class="bi bi-check-circle-fill text-success fs-5"></i>`
                    }
                </td>
            </tr>`;
        });

        $('#pendinPermission').html(html);
        renderPagination();
    }

    /* ===============================
       PAGINATION
    =============================== */
    function renderPagination() {

        const totalPages = Math.ceil(allData.length / perPage);
        let html = '';

        if (totalPages <= 1) {
            $('#pagination').empty();
            return;
        }

        html += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">Prev</a>
            </li>
        `;

        for (let i = 1; i <= totalPages; i++) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }

        html += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
            </li>
        `;

        $('#pagination').html(html);
    }

    /* ===============================
       PAGINATION CLICK
    =============================== */
    $(document).on('click', '#pagination a', function (e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page >= 1) {
            currentPage = page;
            renderPage();
        }
    });

    /* ===============================
       APPROVE
    =============================== */
    $(document).on('click', '.btnApprove', function () {

        const btn = $(this);
        const id = btn.data('id');

        btn.prop('disabled', true)
           .html('<span class="spinner-border spinner-border-sm"></span>');

        $.post('api.php?endpoint=approve_permission',
            { permission_id: id },
            function (res) {
                if (res.status) fetchPermissions();
                else alert(res.message || 'Approve failed');
            },
            'json'
        );
    });

    /* ===============================
       INIT
    =============================== */
    fetchPermissions();

});
</script>


<style>
.permission-table th {
    font-size: .7rem;
    text-transform: uppercase;
    color: #6c757d;
    font-weight: 600;
}

.permission-table td {
    vertical-align: middle;
}

/* Date */
.date-pill {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 20px;
    background: #f8f9fa;
    font-size: .8rem;
}

/* Status */
.status-pill {
    padding: 5px 12px;
    border-radius: 999px;
    font-size: .7rem;
    font-weight: 700;
}
.status-pill.pending {
    background: #fff3cd;
    color: #b7791f;
}
.status-pill.approved {
    background: #d1e7dd;
    color: #0f5132;
}
</style>
