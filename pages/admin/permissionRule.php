<div class="container mt-4">

    <h4 class="mb-3">Attendance / Permission Rules</h4>

    <!-- =====================
         RULE FORM
    ====================== -->
    <div class="card mb-4">
        <div class="card-header">Create / Update Rule</div>
        <div class="card-body">

            <form id="ruleForm">

                <!-- FOR UPDATE -->
                <input type="hidden" name="id" id="rule_id">

                <div class="row">
                    <div class="col-md-3 mb-2">
                        <select name="rule_type" class="form-control" required>
                            <option value="permission">Permission</option>
                            <option value="absence">Absence</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <input type="number" name="limit_count"
                               class="form-control"
                               placeholder="Limit"
                               required>
                    </div>

                    <div class="col-md-2 mb-2">
                        <select name="period_type" class="form-control" required>
                            <option value="week">Per Week</option>
                            <option value="month">Per Month</option>
                            <option value="both">Both</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-2">
                        <input type="date" name="start_date"
                               class="form-control" required>
                    </div>

                    <div class="col-md-2 mb-2 d-flex align-items-center">
                        <input type="checkbox" name="is_active" id="form_is_active" value="1" class="me-2">
                        Activate
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-2" id="saveBtn">
                    Save Rule
                </button>

                <button type="button" class="btn btn-secondary mt-2 d-none" id="cancelEdit">
                    Cancel
                </button>
            </form>

        </div>
    </div>

    <!-- =====================
         RULE TABLE
    ====================== -->
    <div class="card">
        <div class="card-header">All Rules</div>
        <div class="card-body p-0">

            <table class="table table-bordered table-striped mb-0" id="rulesTable">
                <thead class="table-dark">
                    <tr>
                        <th>Type</th>
                        <th>Limit</th>
                        <th>Period</th>
                        <th>Start Date</th>
                        <th>Status</th>
                        <th width="160">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            Loading rules...
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>

</div>

<script>

/* =====================
   LOAD ALL RULES
===================== */
function loadRules() {
    $.get("api.php", { endpoint: "getAllRules" }, function (res) {

        const tbody = $("#rulesTable tbody").empty();

        if (!res.status || res.data.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        No rules found
                    </td>
                </tr>
            `);
            return;
        }

        res.data.forEach(rule => {

            const checked = Number(rule.is_active) === 1 ? "checked" : "";

            tbody.append(`
                <tr>
                    <td>${rule.rule_type}</td>
                    <td>${rule.limit_count}</td>
                    <td>${rule.period_type}</td>
                    <td>${rule.start_date}</td>
                    <td class="text-center">
                        <input type="checkbox"
                            ${checked}
                            ${$("#rule_id").val() ? "disabled" : ""}
                            onchange="toggleRule(${rule.id}, '${rule.rule_type}', this.checked)">
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-warning me-1"
                            onclick="editRule(${rule.id})">
                            Edit
                        </button>
                        <button class="btn btn-sm btn-danger"
                            onclick="deleteRule(${rule.id})">
                            Delete
                        </button>
                    </td>
                </tr>
            `);
        });

    }, "json");
}

/* =====================
   SAVE / UPDATE
===================== */
$("#ruleForm").on("submit", function (e) {
    e.preventDefault();

    const id = $("#rule_id").val();
    const endpoint = id ? "updateRule" : "saveRule";

    $.ajax({
        url: "api.php?endpoint=" + endpoint,
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",

        success: function (res) {
            if (res.status) {
                Swal.fire("Success", res.message, "success");
                resetForm();
                loadRules();
            } else {
                Swal.fire("Error", res.message, "error");
            }
        },

        error: function () {
            Swal.fire("Error", "Failed to process request", "error");
        }
    });
});

/* =====================
   EDIT RULE
===================== */
function editRule(id) {

    $.get("api.php", { endpoint: "getAllRules" }, function (res) {
        const rule = res.data.find(r => r.id == id);
        if (!rule) return;

        $("select[name='rule_type']").val(rule.rule_type).prop("disabled", true);
        $("input[name='limit_count']").val(rule.limit_count);
        $("select[name='period_type']").val(rule.period_type);
        $("input[name='start_date']").val(rule.start_date);
        $("#form_is_active").prop("checked", rule.is_active == 1);

        $("#rule_id").val(rule.id);
        $("#saveBtn").text("Update Rule");
        $("#cancelEdit").removeClass("d-none");

        loadRules();
    }, "json");
}

/* =====================
   RESET FORM
===================== */
$("#cancelEdit").on("click", resetForm);

function resetForm() {
    $("#ruleForm")[0].reset();
    $("#rule_id").val("");
    $("select[name='rule_type']").prop("disabled", false);
    $("#saveBtn").text("Save Rule");
    $("#cancelEdit").addClass("d-none");
    loadRules();
}

/* =====================
   TOGGLE ACTIVE
===================== */
function toggleRule(id, type, checked) {
    $.post("api.php?endpoint=toggleRule", {
        id: id,
        rule_type: type,
        is_active: checked ? 1 : 0
    }, function (res) {
        Swal.fire(
            res.status ? "Updated" : "Error",
            res.message,
            res.status ? "success" : "error"
        );
        loadRules();
    }, "json");
}

/* =====================
   DELETE
===================== */
function deleteRule(id) {
    Swal.fire({
        title: "Delete this rule?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete"
    }).then(result => {
        if (result.isConfirmed) {
            $.post("api.php?endpoint=deleteRule", { id }, function (res) {
                Swal.fire(
                    res.status ? "Deleted" : "Error",
                    res.message,
                    res.status ? "success" : "error"
                );
                loadRules();
            }, "json");
        }
    });
}

/* =====================
   INIT
===================== */
$(document).ready(function () {
    loadRules();
});
</script>
