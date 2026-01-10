<section>
    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
        <div>
            <h3 class="mb-1">Permission Requests</h3>
            <p class="text-muted small mb-0">Review and approve student permission requests</p>
        </div>  
        <button id="btnFetchUsers" class="btn btn-primary">
            <i class="bi bi-arrow-clockwise me-2"></i>Load Requests
        </button>              
    </div>

    <div id="pendinPermission" class="mt-3">
        <!-- Pending users will be appended here -->
    </div>
</section>

<script>
$(document).ready(function () {

    /* ===============================
       FETCH PERMISSIONS
    =============================== */
    $('#btnFetchUsers').on('click', function () {

        $('#pendinPermission').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <div class="text-muted">Loading permission requests...</div>
            </div>
        `);

        $.getJSON('api.php', {
            endpoint: 'fetch_permissions_admin'
        }, function (res) {

            if (!res.status || res.data.length === 0) {
                $('#pendinPermission').html(`
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="bi bi-info-circle me-3 fs-4"></i>
                        <div>No permission requests found.</div>
                    </div>
                `);
                return;
            }

            let html = '';

            res.data.forEach(p => {
                html += `
                    <div class="card mb-3 border-start border-4 ${
                        p.status === 'pending' ? 'border-warning' :
                        p.status === 'approved' ? 'border-success' :
                        'border-danger'
                    }">
                        <div class="card-body">
                            <div class="row g-3">
                                
                                <!-- Student Info -->
                                <div class="col-md-4">
                                    <div class="d-flex align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-0">${p.student_name ?? 'Unknown Student'}</h6>
                                            <small class="text-muted">Student</small>
                                        </div>
                                    </div>
                                    <span class="badge ${
                                        p.status === 'pending' ? 'bg-warning text-dark' :
                                        p.status === 'approved' ? 'bg-success' :
                                        'bg-danger'
                                    }">
                                        ${p.status.toUpperCase()}
                                    </span>
                                </div>

                                <!-- Course Info (EMPHASIZED) -->
                                <div class="col-md-4">
                                    <div class="rounded h-100">
                                        <div class="d-flex align-items-start mb-2">
                                            <div class="flex-grow-1">
                                                <small class="text-muted d-block">Course</small>
                                                <h6 class="mb-0 fw-bold">${p.course_name ?? 'N/A'}</h6>
                                            </div>
                                        </div>
                                        <div class="small text-muted">
                                            <span class="fw-bold"><i class="bi bi-calendar3 me-1"></i>${p.term_name ?? '-'}</span>
                                            <span class="ms-2 fw-bold"><i class="bi bi-clock me-1"></i>${p.time_name ?? '-'}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Date Range (EMPHASIZED) -->
                                <div class="col-md-4">
                                    <div class="bg-info bg-opacity-10 rounded p-3 h-100">
                                        <small class="text-muted d-block mb-3 text-center">
                                            <i class="bi bi-calendar-range me-1"></i>Permission Period
                                        </small>
                                        
                                        <div class="d-flex align-items-center justify-content-center gap-3">
                                            <!-- Start Date -->
                                            <div class="bg-white rounded shadow-sm p-2 text-center" style="min-width: 90px;">
                                                <div class="text-primary text-uppercase small fw-semibold mb-1">
                                                    ${new Date(p.start_date).toLocaleString('en', { month: 'short' })}
                                                </div>
                                                <div class="fs-2 fw-bold lh-1 mb-1">
                                                    ${new Date(p.start_date).getDate()}
                                                </div>
                                                <small class="text-muted">
                                                    ${new Date(p.start_date).getFullYear()}
                                                </small>
                                            </div>
                                            
                                            <!-- Arrow -->
                                            <div class="text-primary">
                                                <i class="bi bi-arrow-right-circle-fill fs-4"></i>
                                            </div>
                                            
                                            <!-- End Date -->
                                            <div class="bg-white rounded shadow-sm p-2 text-center" style="min-width: 90px;">
                                                <div class="text-primary text-uppercase small fw-semibold mb-1">
                                                    ${new Date(p.end_date).toLocaleString('en', { month: 'short' })}
                                                </div>
                                                <div class="fs-2 fw-bold lh-1 mb-1">
                                                    ${new Date(p.end_date).getDate()}
                                                </div>
                                                <small class="text-muted">
                                                    ${new Date(p.end_date).getFullYear()}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reason -->
                                <div class="col-12">
                                    <div class="border-top pt-3">
                                        <strong class="d-block mb-2">
                                            <i class="bi bi-chat-left-text me-2"></i>Reason:
                                        </strong>
                                        <p class="mb-0 text-muted">${p.reason}</p>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                ${
                                    p.status === 'pending'
                                    ? `<div class="col-12">
                                        <div class="d-grid gap-2 d-md-flex justify-content-md-end border-top pt-3">
                                            <button 
                                                class="btn btn-success btnApprove"
                                                data-id="${p.permission_id}">
                                                <i class="bi bi-check-circle me-2"></i>Approve Request
                                            </button>
                                        </div>
                                    </div>`
                                    : ''
                                }
                            </div>
                        </div>
                    </div>
                `;
            });

            $('#pendinPermission').html(html);
        });
    });

    /* ===============================
       APPROVE PERMISSION (FIXED)
    =============================== */
    $(document).on('click', '.btnApprove', function () {

        const btn = $(this);
        const permissionId = btn.data('id');

        btn.prop('disabled', true)
           .html('<span class="spinner-border spinner-border-sm me-2"></span>Approving');

        $.post('api.php?endpoint=approve_permission', {
            permission_id: permissionId
        }, function (res) {

            if (res.status) {
                const card = btn.closest('.card');

                card.find('.badge')
                    .removeClass('bg-warning text-dark')
                    .addClass('bg-success')
                    .text('APPROVED');

                card.removeClass('border-warning')
                    .addClass('border-success');

                btn.remove();
            } else {
                btn.prop('disabled', false)
                   .html('<i class="bi bi-check-circle me-2"></i>Approve Request');
                // alert(res.message);
            }

        }, 'json').fail(function () {
            btn.prop('disabled', false)
               .html('<i class="bi bi-check-circle me-2"></i>Approve Request');
            alert('Network error');
        });
    });

});
</script>
