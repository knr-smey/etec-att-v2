<section>
    <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-1 fw-semibold">Blacklist Students</h4>
            <p class="text-muted small mb-0">Students with hard lock attendance status</p>
        </div>
        <button class="btn btn-sm btn-dark" id="btnRefreshBlacklist">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
        </button>
       
    </div>
    <div class="mb-3">
        <input 
            type="text" 
            id="searchBlacklist" 
            class="form-control form-control-sm w-25 shadow-none"
            placeholder="Search student by name..."
        >
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Gender</th>
                    <th>Phone</th>
                    <th>Course</th>
                    <th>Blocked At</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody id="blacklistTbody">
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <div class="spinner-border spinner-border-sm text-dark"></div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<script>
$(function () {
    let blacklistData = [];

    function esc(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;');
    }

    function renderRows(rows) {
        const $tbody = $('#blacklistTbody');

        if (!rows || !rows.length) {
            $tbody.html(`
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        No blacklisted students found
                    </td>
                </tr>
            `);
            return;
        }

        const html = rows.map((row, index) => {
            return `
                <tr>
                    <td>${index + 1}</td>
                    <td>${esc(row.full_name)}</td>
                    <td>${esc(row.gender)}</td>
                    <td>${esc(row.tel)}</td>
                    <td>${esc(row.course)}</td>
                    <td>${esc(row.blocked_at)}</td>
                    <td class="text-end">
                        <button
                            class="btn btn-sm btn-success btnUnblock"
                            data-id="${esc(row.block_id)}"
                        >
                            Unblock
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        $tbody.html(html);
    }

    function fetchBlacklist() {
        const $tbody = $('#blacklistTbody');

        $tbody.html(`
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    <div class="spinner-border spinner-border-sm text-dark"></div>
                </td>
            </tr>
        `);

        $.getJSON('api.php', { endpoint: 'fetch_blacklist_students' })
            .done(function (res) {

                if (!res || !res.status) {
                    $tbody.html(`
                        <tr>
                            <td colspan="7" class="text-center text-danger py-4">
                                ${esc(res?.message || 'Failed to fetch blacklist students')}
                            </td>
                        </tr>
                    `);
                    return;
                }

                blacklistData = res.data || [];
                renderRows(blacklistData);

            })
            .fail(function () {

                $tbody.html(`
                    <tr>
                        <td colspan="7" class="text-center text-danger py-4">
                            Network error while fetching blacklist students
                        </td>
                    </tr>
                `);

            });
    }

    // 🔎 Search filter
    $('#searchBlacklist').on('keyup', function () {

        const keyword = $(this).val().toLowerCase();

        const filtered = blacklistData.filter(row =>
            (row.full_name || '').toLowerCase().includes(keyword) ||
            (row.tel || '').toLowerCase().includes(keyword) ||
            (row.course || '').toLowerCase().includes(keyword)
        );

        renderRows(filtered);

    });

    // 🔓 Unblock student
    $(document).on('click', '.btnUnblock', function () {

        const $btn = $(this);
        const blockId = parseInt($btn.data('id'), 10);

        const original = $btn.html();
        $btn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm"></span>');

        $.post(
            'api.php?endpoint=unblock_blacklist_student',
            { block_id: blockId },
            function (res) {

                if (!res || !res.status) {
                    $btn.prop('disabled', false).html(original);
                    return;
                }

                fetchBlacklist();

            },
            'json'
        ).fail(function () {

            $btn.prop('disabled', false).html(original);

        });

    });

    // 🔄 Refresh button
    $('#btnRefreshBlacklist').on('click', fetchBlacklist);

    // initial load
    fetchBlacklist();

});
</script>