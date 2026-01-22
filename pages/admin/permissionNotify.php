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
</section>

<script>
$(function () {

  let allData = [];
  let filteredData = []; // ✅ NEW: data after search filter
  let currentPage = 1;
  const perPage = 8;
  let firstLoad = true;

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

        // ✅ Render top counts
        setTopCounts(
          counts.absence_approved ?? 0,
          counts.permission_approved ?? 0
        );

        if (!Array.isArray(list) || !list.length) {
          allData = [];
          filteredData = [];
          renderEmpty(false); // don't reset counts (already set above)
          updateSearchHint();
          return;
        }

        allData = list;
        applySearch(); // ✅ NEW: will set filteredData
      })
      .fail(() => {
        renderError();
      });
  }

  function setTopCounts(absence, permission) {
    absence = parseInt(absence, 10) || 0;
    permission = parseInt(permission, 10) || 0;

    $('#countAbsenceApproved').text(absence);
    $('#countPermissionApproved').text(permission);
    $('#countTotalApproved').text(absence + permission);
  }

  /* ===============================
     ✅ SEARCH FILTER (by student name)
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

    if ($('#searchName').val().trim() && total > 0) {
      $('#searchHint').show();
    } else {
      $('#searchHint').hide();
    }
  }

  // debounce to avoid heavy rendering while typing
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
    const data = filteredData; // ✅ use filtered data

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

      // absence: status 0/1
      // permission: status 'pending'/'approved'
      const isApproved = isAbsence
        ? (parseInt(p.status, 10) === 1)
        : (String(p.status).toLowerCase() === 'approved');

      const isPending = !isApproved;

      // ✅ per-student counts (from backend window function)
      const absStu = parseInt(p.absence_approved_by_student, 10) || 0;
      const perStu = parseInt(p.permission_approved_by_student, 10) || 0;

      return `
        <tr data-id="${escapeHtml(p.request_id)}" data-type="${escapeHtml(p.request_type)}">

          <!-- Student + per-student count -->
          <td>
            <div class="d-flex flex-column">
              <strong>${escapeHtml(p.student_name)}</strong>
              <small class="text-muted">
                Abs: <b>${absStu}</b> | Per: <b>${perStu}</b>
              </small>
            </div>
          </td>

          <!-- Course -->
          <td class="text-primary fw-semibold">${escapeHtml(p.course)}</td>

          <!-- Period -->
          <td>
            ${
              isAbsence
                ? `<span class="badge bg-danger">ABSENCE LIMIT</span>`
                : `<span class="date-pill">
                    <i class="bi bi-calendar3 me-1"></i>
                    ${escapeHtml(p.start_date)} → ${escapeHtml(p.end_date)}
                  </span>`
            }
          </td>

          <!-- Reason -->
          <td class="text-muted small">${escapeHtml(p.reason || '-')}</td>

          <!-- Status -->
          <td class="text-center">
            <span class="status-pill ${isPending ? 'pending' : 'approved'}">
              ${isPending ? 'PENDING' : 'APPROVED'}
            </span>
          </td>

          <!-- Action -->
          <td class="text-end">
            ${
              isPending
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
    const data = filteredData; // ✅ use filtered data
    const totalPages = Math.ceil(data.length / perPage);

    if (totalPages <= 1) {
      $('#pagination').empty();
      return;
    }

    let html = `
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

  $(document).on('click', '#pagination a', function (e) {
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
     APPROVE (NO BLINK)
  =============================== */
  $(document).on('click', '.btnApprove', function () {

    const btn  = $(this);
    const id   = btn.data('id');
    const type = btn.data('type');

    const endpoint = (type === 'absence')
      ? 'approve_absence_block'
      : 'approve_permission';

    const payload = { id: id }; // ✅ FIX

    btn.prop('disabled', true)
      .html('<span class="spinner-border spinner-border-sm"></span>');

    $.post(`api.php?endpoint=${endpoint}`, payload, function (res) {
      if (!res || !res.status) {
        btn.prop('disabled', false).text('Approve');
        console.log(res?.message || 'Approve failed');
        return;
      }

      const row = btn.closest('tr');

      // ✅ UI status change
      row.find('.status-pill')
        .removeClass('pending')
        .addClass('approved')
        .text('APPROVED');

      btn.replaceWith('<i class="bi bi-check-circle-fill text-success fs-5"></i>');

      // ✅ Update top counters instantly
      const abs = parseInt($('#countAbsenceApproved').text(), 10) || 0;
      const per = parseInt($('#countPermissionApproved').text(), 10) || 0;

      if (type === 'absence') {
        $('#countAbsenceApproved').text(abs + 1);
      } else {
        $('#countPermissionApproved').text(per + 1);
      }

      const absNew = parseInt($('#countAbsenceApproved').text(), 10) || 0;
      const perNew = parseInt($('#countPermissionApproved').text(), 10) || 0;
      $('#countTotalApproved').text(absNew + perNew);

      // ✅ Update per-student count shown in the row
      // (this row only — other rows same student not updated unless you refresh)
      const small = row.find('small');
      const text = small.text(); // "Abs: 2 | Per: 3"
      const match = text.match(/Abs:\s*(\d+)\s*\|\s*Per:\s*(\d+)/i);

      if (match) {
        let absStu = parseInt(match[1], 10) || 0;
        let perStu = parseInt(match[2], 10) || 0;

        if (type === 'absence') absStu += 1;
        else perStu += 1;

        small.html(`Abs: <b>${absStu}</b> | Per: <b>${perStu}</b>`);
      }

      // ✅ Keep search + pagination stable after approve:
      // just update UI, but if you want to remove approved from list, uncomment:
      // fetchRequests();

    }, 'json')
    .fail(() => {
      btn.prop('disabled', false).text('Approve');
      alert('Network error');
    });
  });

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
</style>
