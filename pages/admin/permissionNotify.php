<section>
  <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h4 class="mb-1 fw-semibold">Permission Requests</h4>
      <p class="text-muted small mb-0">Review and approve student permission requests</p>
    </div>

    <!-- ✅ COUNTS -->
    <div class="d-flex gap-2 flex-wrap">
      <span class="badge bg-success-subtle text-success border">
        Absence Approved: <span id="countAbsenceApproved">0</span>
      </span>
      <span class="badge bg-primary-subtle text-primary border">
        Permission Approved: <span id="countPermissionApproved">0</span>
      </span>
      <span class="badge bg-dark-subtle text-dark border">
        Total Approved: <span id="countTotalApproved">0</span>
      </span>
    </div>
  </div>

  <!-- ✅ FILTER SEARCH (by student name) -->
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
    <div class="input-group input-group-sm" style="max-width: 320px;">
      <span class="input-group-text bg-light">
        <i class="bi bi-search"></i>
      </span>
      <input
        type="text"
        id="searchName"
        class="form-control shadow-none border"
        placeholder="Search student name..."
        autocomplete="off"
      >
      <button class="btn btn-outline-secondary" id="btnClearSearch" type="button">Clear</button>
    </div>

    <small class="text-muted" id="searchHint" style="display:none;">
      Showing <b id="shownCount">0</b> of <b id="totalCount">0</b>
    </small>
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
      <tbody id="pendinPermission"></tbody>
    </table>
  </div>

  <!-- Pagination -->
  <nav class="mt-3">
    <ul id="pagination" class="pagination pagination-sm justify-content-center"></ul>
  </nav>

  <!-- Approve Modal (Admin Comment Required) -->
  <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header">
          <h5 class="modal-title fw-semibold">Approve Request</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="mb-2 text-muted small">
            Student: <b id="modalStudentName">-</b><br>
            Type: <b id="modalReqType">-</b>
          </div>

          <label class="form-label fw-semibold">Admin Comment <span class="text-danger">*</span></label>
          <textarea
            id="adminComment"
            class="form-control"
            rows="3"
            placeholder="Write reason why you approve..."
          ></textarea>

          <div id="modalError" class="alert alert-danger py-2 mt-3 d-none"></div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-success" id="btnModalApprove">
            Approve
          </button>
        </div>
      </div>
    </div>
  </div>

</section>

<script>
$(function () {

  let allData = [];
  let filteredData = [];
  let currentPage = 1;
  const perPage = 8;
  let firstLoad = true;

  /* ===============================
     ✅ BOOTSTRAP MODAL
  =============================== */
  const approveModalEl = document.getElementById('approveModal');
  const approveModal = approveModalEl ? new bootstrap.Modal(approveModalEl) : null;

  // store selected approve info
  let selectedApprove = { id: null, type: null, btn: null, row: null };

  /* ===============================
     FETCH DATA
  =============================== */
  function fetchRequests() {
    if (firstLoad) {
      $('#pendinPermission').html(`
        <tr>
          <td colspan="6" class="text-center py-4">
            <div class="spinner-border spinner-border-sm text-primary"></div>
          </td>
        </tr>
      `);
    }

    $.getJSON('api.php', { endpoint: 'fetch_absence_permission_admin' })
      .done(res => {
        firstLoad = false;

        if (!res || !res.status || !res.data) {
          allData = [];
          filteredData = [];
          renderEmpty();
          updateSearchHint();
          return;
        }

        const counts = res.data.counts || {};
        const list   = res.data.list || [];

        setTopCounts(
          counts.absence_approved ?? 0,
          counts.permission_approved ?? 0
        );

        if (!Array.isArray(list) || !list.length) {
          allData = [];
          filteredData = [];
          renderEmpty(false);
          updateSearchHint();
          return;
        }

        allData = list;
        applySearch();
      })
      .fail(() => renderError());
  }

  function setTopCounts(absence, permission) {
    absence = parseInt(absence, 10) || 0;
    permission = parseInt(permission, 10) || 0;

    $('#countAbsenceApproved').text(absence);
    $('#countPermissionApproved').text(permission);
    $('#countTotalApproved').text(absence + permission);
  }

  /* ===============================
     SEARCH FILTER
  =============================== */
  function applySearch() {
    const q = String($('#searchName').val() || '').trim().toLowerCase();

    if (!q) {
      filteredData = [...allData];
    } else {
      filteredData = allData.filter(p => {
        const name = String(p.student_name || '').toLowerCase();
        return name.includes(q);
      });
    }

    currentPage = 1;
    renderPage();
    updateSearchHint();
  }

  function updateSearchHint() {
    const total = allData.length || 0;
    const shown = filteredData.length || 0;

    $('#totalCount').text(total);
    $('#shownCount').text(shown);

    if ($('#searchName').val().trim() && total > 0) $('#searchHint').show();
    else $('#searchHint').hide();
  }

  let searchTimer = null;
  $('#searchName').on('input', function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applySearch, 200);
  });

  $('#btnClearSearch').on('click', function () {
    $('#searchName').val('');
    applySearch();
    $('#searchName').focus();
  });

  /* ===============================
     RENDER PAGE
  =============================== */
  function renderPage() {
    const data = filteredData;

    const totalPages = Math.ceil(data.length / perPage) || 1;
    if (currentPage > totalPages) currentPage = totalPages;

    const start = (currentPage - 1) * perPage;
    const pageData = data.slice(start, start + perPage);

    if (!pageData.length) {
      $('#pendinPermission').html(`
        <tr>
          <td colspan="6" class="text-center text-muted py-4">
            No requests found
          </td>
        </tr>
      `);
      $('#pagination').empty();
      return;
    }

    const html = pageData.map(p => {
      const isAbsence = p.request_type === 'absence';
      const isHardLock = isAbsence && String(p.block_mode || '').toLowerCase() === 'hard_lock';

      const isApproved = isAbsence
        ? (parseInt(p.status, 10) === 1 || isHardLock)
        : (String(p.status).toLowerCase() === 'approved');

      const isPending = !isApproved;
      const canApprove = isPending && !isHardLock;

      const absStu = parseInt(p.absence_approved_by_student, 10) || 0;
      const perStu = parseInt(p.permission_approved_by_student, 10) || 0;

      return `
        <tr data-id="${escapeHtml(p.request_id)}" data-type="${escapeHtml(p.request_type)}">

          <td>
            <div class="d-flex flex-column">
              <strong>${escapeHtml(p.student_name)}</strong>
              <small class="text-muted">
                Abs: <b>${absStu}</b> | Per: <b>${perStu}</b>
              </small>
            </div>
          </td>

          <td class="text-primary fw-semibold">${escapeHtml(p.course)}</td>

          <td>
            ${
              isAbsence
                ? `<span class="badge ${isHardLock ? 'bg-dark' : 'bg-danger'}">${isHardLock ? 'HARD LOCK' : 'ABSENCE LIMIT'}</span>`
                : `<span class="date-pill">
                    <i class="bi bi-calendar3 me-1"></i>
                    ${escapeHtml(p.start_date)} → ${escapeHtml(p.end_date)}
                  </span>`
            }
          </td>

          <td class="text-muted small">${escapeHtml(p.reason || '-')}</td>

          <td class="text-center">
            <span class="status-pill ${isHardLock ? 'approved' : (isPending ? 'pending' : 'approved')}">
              ${isHardLock ? 'HARD LOCK' : (isPending ? 'PENDING' : 'APPROVED')}
            </span>
          </td>

          <td class="text-end">
            ${
              canApprove
                ? `<button class="btn btn-success btn-sm btnApprove"
                      data-id="${escapeHtml(p.request_id)}"
                      data-type="${escapeHtml(p.request_type)}">
                      Approve
                  </button>`
                : `<i class="bi bi-check-circle-fill text-success fs-5"></i>`
            }
          </td>
        </tr>
      `;
    }).join('');

    $('#pendinPermission').html(html);
    renderPagination();
  }

  /* ===============================
     PAGINATION
  =============================== */
  function renderPagination() {
    const totalPages = Math.ceil(filteredData.length / perPage);

    if (totalPages <= 1) {
      $('#pagination').empty();
      return;
    }

    const pageItems = buildPageItems(totalPages, currentPage);

    let html = `
      <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${currentPage - 1}">Prev</a>
      </li>
    `;

    for (const item of pageItems) {
      if (item === '...') {
        html += `
          <li class="page-item disabled d-none d-sm-inline">
            <span class="page-link">...</span>
          </li>
        `;
        continue;
      }

      html += `
        <li class="page-item ${item === currentPage ? 'active' : ''}">
          <a class="page-link" href="#" data-page="${item}">${item}</a>
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

  // Keep pagination compact for large datasets: 1 ... (window) ... N
  function buildPageItems(totalPages, page) {
    if (totalPages <= 7) {
      return Array.from({ length: totalPages }, (_, i) => i + 1);
    }

    const items = [1];
    let start = page - 1;
    let end = page + 1;

    if (page <= 4) {
      start = 2;
      end = 5;
    } else if (page >= totalPages - 3) {
      start = totalPages - 4;
      end = totalPages - 1;
    }

    if (start > 2) items.push('...');
    for (let i = start; i <= end; i++) {
      if (i > 1 && i < totalPages) items.push(i);
    }
    if (end < totalPages - 1) items.push('...');

    items.push(totalPages);
    return items;
  }

  $(document)
    .off('click.paginate', '#pagination a')
    .on('click.paginate', '#pagination a', function (e) {
      e.preventDefault();
      const page = +$(this).data('page');
      const totalPages = Math.ceil(filteredData.length / perPage);

      if (page >= 1 && page <= totalPages) {
        currentPage = page;
        renderPage();
      }
    });

  /* ===============================
     EMPTY / ERROR
  =============================== */
  function renderEmpty(resetCounts = true) {
    $('#pendinPermission').html(`
      <tr>
        <td colspan="6" class="text-center text-muted py-4">
          No requests found
        </td>
      </tr>
    `);
    $('#pagination').empty();
    if (resetCounts) setTopCounts(0, 0);
  }

  function renderError() {
    $('#pendinPermission').html(`
      <tr>
        <td colspan="6" class="text-center text-danger py-4">
          Failed to load data
        </td>
      </tr>
    `);
  }

  /* ===============================
     ✅ APPROVE: OPEN MODAL
     (IMPORTANT: off/on to avoid multiple handlers)
  =============================== */
  $(document)
    .off('click.approve', '.btnApprove')
    .on('click.approve', '.btnApprove', function () {

      if (!approveModal) {
        alert("Modal not found (#approveModal).");
        return;
      }

      const btn  = $(this);
      const id   = btn.data('id');
      const type = btn.data('type');

      const row = btn.closest('tr');
      const studentName = row.find('strong').first().text().trim() || '-';

      selectedApprove = { id, type, btn, row };

      $('#modalStudentName').text(studentName);
      $('#modalReqType').text(type === 'absence' ? 'absence block' : 'permission');
      $('#adminComment').val('');
      $('#modalError').addClass('d-none').text('');

      $('#btnModalApprove').prop('disabled', false).text('Approve');

      approveModal.show();
      setTimeout(() => $('#adminComment').focus(), 200);
    });

  /* ===============================
     ✅ APPROVE: SUBMIT FROM MODAL
     (IMPORTANT: off/on to avoid multiple handlers)
  =============================== */
  $('#btnModalApprove')
    .off('click.approveSubmit')
    .on('click.approveSubmit', function () {

      const { id, type, btn, row } = selectedApprove;

      const comment = String($('#adminComment').val() || '').trim();
      if (!comment) {
        $('#modalError').removeClass('d-none').text('Admin comment is required.');
        return;
      }

      const endpoint = (type === 'absence')
        ? 'approve_absence_block'
        : 'approve_permission';

      const modalBtn = $(this);
      modalBtn.prop('disabled', true)
              .html('<span class="spinner-border spinner-border-sm"></span> Approving...');

      $.post(`api.php?endpoint=${endpoint}`, { id, admin_comment: comment }, function (res) {
        modalBtn.prop('disabled', false).text('Approve');

        if (!res || !res.status) {
          $('#modalError').removeClass('d-none').text(res?.message || 'Approve failed');
          return;
        }

        approveModal.hide();

        // UI update
        row.find('.status-pill').removeClass('pending').addClass('approved').text('APPROVED');
        btn.replaceWith('<i class="bi bi-check-circle-fill text-success fs-5"></i>');

        // update top counters
        const abs = parseInt($('#countAbsenceApproved').text(), 10) || 0;
        const per = parseInt($('#countPermissionApproved').text(), 10) || 0;

        if (type === 'absence') $('#countAbsenceApproved').text(abs + 1);
        else $('#countPermissionApproved').text(per + 1);

        const absNew = parseInt($('#countAbsenceApproved').text(), 10) || 0;
        const perNew = parseInt($('#countPermissionApproved').text(), 10) || 0;
        $('#countTotalApproved').text(absNew + perNew);

        // update per-student count in row
        const small = row.find('small');
        const text = small.text();
        const match = text.match(/Abs:\s*(\d+)\s*\|\s*Per:\s*(\d+)/i);

        if (match) {
          let absStu = parseInt(match[1], 10) || 0;
          let perStu = parseInt(match[2], 10) || 0;
          if (type === 'absence') absStu += 1;
          else perStu += 1;
          small.html(`Abs: <b>${absStu}</b> | Per: <b>${perStu}</b>`);
        }

      }, 'json')
      .fail(() => {
        modalBtn.prop('disabled', false).text('Approve');
        $('#modalError').removeClass('d-none').text('Network error');
      });
    });

  // reset on modal close
  if (approveModalEl) {
    approveModalEl.addEventListener('hidden.bs.modal', () => {
      selectedApprove = { id: null, type: null, btn: null, row: null };
      $('#modalError').addClass('d-none').text('');
      $('#adminComment').val('');
      $('#btnModalApprove').prop('disabled', false).text('Approve');
    });
  }

  /* ===============================
     SMALL UTILS (XSS SAFE)
  =============================== */
  function escapeHtml(str) {
    return String(str ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  /* ===============================
     INIT
  =============================== */
  fetchRequests();

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

#pagination {
  flex-wrap: wrap;
  gap: 4px;
}

#pagination .page-link {
  min-width: 34px;
  text-align: center;
}
</style>
