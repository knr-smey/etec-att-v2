<section>

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
    <div>
      <h4 class="mb-1 fw-semibold">Discount Rules</h4>
      <p class="text-muted mb-0">Manage score range discounts (Admin CRUD)</p>
    </div>

    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary" id="btnRefresh">
        <i class="bi bi-arrow-clockwise"></i> Refresh
      </button>
      <button class="btn btn-primary" id="btnAdd">
        <i class="bi bi-plus-lg"></i> Add Discount
      </button>
    </div>
  </div>

  <!-- Stats + Search -->
  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <div class="bg-white border rounded-4 p-3 shadow-sm">
        <div class="small text-muted">Total Rules</div>
        <div class="fs-4 fw-bold" id="countTotal">0</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="bg-white border rounded-4 p-3 shadow-sm">
        <div class="small text-muted">Active</div>
        <div class="fs-4 fw-bold text-success" id="countActive">0</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="bg-white border rounded-4 p-3 shadow-sm">
        <div class="small text-muted">Inactive</div>
        <div class="fs-4 fw-bold text-danger" id="countInactive">0</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="bg-white border rounded-4 p-3 shadow-sm">
        <div class="small text-muted mb-1">Search</div>
        <input type="text" id="searchBox" class="form-control" placeholder="Title / description...">
      </div>
    </div>
  </div>

  <!-- Table -->
  <div class="bg-white border rounded-4 shadow-sm overflow-hidden">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:70px;">#</th>
            <th>Title</th>
            <th>Description</th>
            <th class="text-center">Range</th>
            <th class="text-center">Discount</th>
            <th class="text-center">Status</th>
            <th class="text-end" style="width:170px;">Actions</th>
          </tr>
        </thead>
        <tbody id="discountRows">
          <tr><td colspan="7" class="text-center text-muted py-4">Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </div>

</section>

<!-- Modal -->
<div class="modal fade" id="discountModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header">
        <h5 class="modal-title fw-semibold" id="modalTitle">Add Discount</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="discountId">

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-medium">Title</label>
            <input type="text" class="form-control" id="title" placeholder="e.g. Rank A">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-medium">Discount Percent (%)</label>
            <input type="number" step="0.01" class="form-control" id="discount_percent" placeholder="e.g. 70">
          </div>

          <div class="col-md-12">
            <label class="form-label fw-medium">Description</label>
            <textarea class="form-control" id="description" rows="2" placeholder="Optional"></textarea>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-medium">Min Score (inclusive)</label>
            <input type="number" step="0.01" class="form-control" id="min_score" placeholder="e.g. 90">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-medium">Max Score (exclusive)</label>
            <input type="number" step="0.01" class="form-control" id="max_score" placeholder="e.g. 95">
          </div>

          <div class="col-md-12">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="is_active" checked>
              <label class="form-check-label" for="is_active">Active</label>
            </div>
            <div class="small text-muted mt-1">Rule: <b>min ≤ score &lt; max</b> (avoid overlap)</div>
          </div>
        </div>

        <div class="alert alert-danger mt-3 d-none" id="formError"></div>
        <div class="alert alert-success mt-3 d-none" id="formSuccess"></div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="btnSave">
          <span class="spinner-border spinner-border-sm me-2 d-none" id="saveSpinner"></span>
          Save
        </button>
      </div>
    </div>
  </div>
</div>
<script>
    $(function () {
    const API = "api.php";
    const modal = new bootstrap.Modal(document.getElementById("discountModal"));
    let rules = [];

    // ---------- helpers ----------
    const esc = (s) => $("<div>").text(s ?? "").html();
    const showErr = (msg) => $("#formError").removeClass("d-none").text(msg);
    const showOk  = (msg) => $("#formSuccess").removeClass("d-none").text(msg);
    const resetMsg = () => $("#formError,#formSuccess").addClass("d-none").text("");

    function stats(list) {
        $("#countTotal").text(list.length);
        $("#countActive").text(list.filter(x => String(x.is_active) === "1").length);
        $("#countInactive").text(list.filter(x => String(x.is_active) !== "1").length);
    }

    function render(list) {
        const $tb = $("#discountRows");
        $tb.empty();

        if (!list.length) {
        $tb.append(`<tr><td colspan="7" class="text-center text-muted py-4">No discounts found</td></tr>`);
        return;
        }

        list.forEach((d, i) => {
        const active = String(d.is_active) === "1";
        const badge = active
            ? `<span class="badge text-bg-success">Active</span>`
            : `<span class="badge text-bg-secondary">Inactive</span>`;

        $tb.append(`
            <tr data-id="${d.id}">
            <td>${i + 1}</td>
            <td class="fw-semibold">${esc(d.title)}</td>
            <td class="text-muted">${esc(d.description || "")}</td>
            <td class="text-center">
                <span class="badge text-bg-light border">${esc(d.min_score)} - ${esc(d.max_score)}</span>
            </td>
            <td class="text-center">
                <span class="badge text-bg-primary">${esc(d.discount_percent)}%</span>
            </td>
            <td class="text-center">${badge}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-primary me-1 btnEdit">
                <i class="bi bi-pencil"></i> Edit
                </button>
                <button class="btn btn-sm btn-outline-danger btnDel">
                <i class="bi bi-trash"></i> Delete
                </button>
            </td>
            </tr>
        `);
        });
    }

    function applySearch() {
        const q = ($("#searchBox").val() || "").toLowerCase().trim();
        if (!q) return render(rules);

        const filtered = rules.filter(r =>
        String(r.title || "").toLowerCase().includes(q) ||
        String(r.description || "").toLowerCase().includes(q)
        );
        render(filtered);
    }

    function fetchAll() {
        $("#discountRows").html(`<tr><td colspan="7" class="text-center text-muted py-4">Loading...</td></tr>`);
        $.ajax({
        url: API,
        method: "GET",
        dataType: "json",
        data: { endpoint: "get_discounts" }, // ✅ GET endpoint ok
        success: function (res) {
            if (!res.status) {
            $("#discountRows").html(
                `<tr><td colspan="7" class="text-center text-danger py-4">${esc(res.message || "Failed")}</td></tr>`
            );
            return;
            }
            rules = res.data || [];
            stats(rules);
            applySearch();
        },
        error: function () {
            $("#discountRows").html(`<tr><td colspan="7" class="text-center text-danger py-4">Server error</td></tr>`);
        }
        });
    }

    function fillForm(d) {
        $("#discountId").val(d?.id || "");
        $("#title").val(d?.title || "");
        $("#description").val(d?.description || "");
        $("#min_score").val(d?.min_score ?? "");
        $("#max_score").val(d?.max_score ?? "");
        $("#discount_percent").val(d?.discount_percent ?? "");
        $("#is_active").prop("checked", String(d?.is_active ?? "1") === "1");
    }

    function validateForm() {
        const title = $("#title").val().trim();
        const min = parseFloat($("#min_score").val());
        const max = parseFloat($("#max_score").val());
        const dis = parseFloat($("#discount_percent").val());

        if (!title) return "Title is required";
        if (Number.isNaN(min) || Number.isNaN(max)) return "Min/Max score required";
        if (min >= max) return "Min score must be less than max score";
        if (Number.isNaN(dis)) return "Discount percent required";
        if (dis < 0 || dis > 100) return "Discount must be between 0 and 100";
        return null;
    }

    function setSaving(isSaving) {
        $("#btnSave").prop("disabled", isSaving);
        $("#saveSpinner").toggleClass("d-none", !isSaving);
    }

    // ---------- events ----------
    $("#btnRefresh").on("click", fetchAll);
    $("#searchBox").on("input", applySearch);

    $("#btnAdd").on("click", function () {
        resetMsg();
        $("#modalTitle").text("Add Discount");
        fillForm(null);
        modal.show();
    });

    $("#discountRows").on("click", ".btnEdit", function () {
        resetMsg();
        const id = $(this).closest("tr").data("id");
        const d = rules.find(x => String(x.id) === String(id));
        $("#modalTitle").text("Edit Discount");
        fillForm(d);
        modal.show();
    });

        // ✅ DELETE (endpoint in URL)
        $("#discountRows").on("click", ".btnDel", function () {
        const id = $(this).closest("tr").data("id");

        Swal.fire({
        title: "Are you sure?",
        text: "This discount rule will be permanently deleted!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        reverseButtons: true
        }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
        url: "api.php?endpoint=delete_discount",
        method: "POST",
        dataType: "json",
        data: { id: id },
        success: function (res) {
            if (!res.status) {
            return Swal.fire("Error", res.message || "Delete failed", "error");
            }

            Swal.fire({
            icon: "success",
            title: "Deleted!",
            text: "Discount rule has been deleted.",
            timer: 1200,
            showConfirmButton: false
            });

            fetchAll(); // reload table
        },
        error: function () {
            Swal.fire("Error", "Server error", "error");
        }
        });
        });
        });


    // ✅ SAVE (Create/Update) (endpoint in URL)
    $("#btnSave").on("click", function () {
        resetMsg();

        const err = validateForm();
        if (err) return showErr(err);

        const id = $("#discountId").val();
        const endpoint = id ? "update_discount" : "create_discount";

        const payload = {
        title: $("#title").val().trim(),
        description: $("#description").val().trim(),
        min_score: $("#min_score").val(),
        max_score: $("#max_score").val(),
        discount_percent: $("#discount_percent").val(),
        is_active: $("#is_active").is(":checked") ? 1 : 0
        };
        if (id) payload.id = id;

        setSaving(true);

        $.ajax({
        url: API + "?endpoint=" + endpoint, // ✅ endpoint in URL
        method: "POST",
        dataType: "json",
        data: payload, // ✅ no endpoint in POST
        success: function (res) {
            if (!res.status) {
            setSaving(false);
            return showErr(res.message || "Save failed");
            }
            showOk(res.message || "Saved");
            setTimeout(() => {
            modal.hide();
            setSaving(false);
            fetchAll();
            }, 400);
        },
        error: function () {
            setSaving(false);
            showErr("Server error");
        }
        });
    });

    // init
    fetchAll();
    });
</script>

