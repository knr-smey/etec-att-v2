<section>
    <h3 class="mb-0">Class Type</h3>
    <p class="text-secondary mb-3">Manage Your Class Types of school</p>

    <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
        <form class="d-flex col-3 border rounded bg-white">
            <input type="text" id="searchClassType" placeholder="Search Class Type..." class="form-control shadow-none border-0 bg-transparent">
            <button class="btn"><i class="bi bi-search"></i></button>
        </form>
        <button class="btn btn-light border" data-bs-toggle="modal" data-bs-target="#addClassTypeModal">
            Add Class Type
        </button>
    </div>

    <!-- Success Alert -->
    <div id="successAlert" class="alert alert-success alert-dismissible fade show mt-3 " style="display:none;" role="alert">
        <span id="successMessage"></span>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <table class="table align-middle">
        <thead>
        <tr>
            <td class="text-secondary">#</td>
            <td class="text-secondary">Class Type</td>
            <td class="text-secondary">Created_by</td>
            <td class="text-secondary">Created_at</td>
            <td class="text-center text-secondary">Action</td>
        </tr>
        </thead>
        <tbody id="classTypeTableBody">
            
        </tbody>
    </table>

    <!-- Add Modal -->
    <div class="modal fade" id="addClassTypeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Add Class Type</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="addClassTypeForm">
            <div class="modal-body">
                <label for="" class="form-label">Class Type</label>
                <input type="text" id="classtype" class="form-control shadow-none border" placeholder="Class Type Name" required>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btnAdd">Add</button>
            </div>
            </form>
        </div>
        </div>
    </div>

    <!-- Update Modal -->
    <div class="modal fade" id="updateClassTypeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Update Class Type</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <form id="updateClassTypeForm">
                <div class="modal-body">
                    <input type="hidden" id="updateClassTypeId">
                    <label for="" class="form-label">Update Class Type</label>
                    <input type="text" id="updateClassTypeName" class="form-control shadow-none border" required>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnUpdate">Update</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteClassTypeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Delete Class Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="deleteClassTypeForm">
                    <div class="modal-body">
                        <p>Are you sure you want to delete this class type?</p>
                        <input type="hidden" id="deleteClassTypeId">
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" id="btndelete">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<script>
    $(document).ready(function(){

        let classType;

        function showAlert(message) {
            $('#successMessage').text(message);
            $('#successAlert').stop(true, true).fadeIn(); // Stop previous animations
            setTimeout(function() {
                $('#successAlert').fadeOut('slow');
            }, 3000); // Each alert fades out after 3 seconds
        }

        function setLoading(btn, isLoading){
            if(isLoading){
                btn.prop('disabled', true).append(' <span class="spinner-border spinner-border-sm" role="status"></span>');
            } else {
                btn.prop('disabled', false);
                btn.find('.spinner-border').remove();
            }
        }

        function renderTable(data){
            let tbody = '';
            if(data.length > 0){
                let count = 0;
                $.each(data, function(i, ct){
                    count++
                    tbody += `<tr>
                        <td>${count}</td>
                        <td>${ct.class_type}</td>
                        <td><span class="bg-primary-subtle text-primary px-2 rounded">${ct.created_by}</span></td>
                        <td><span class="bg-secondary-subtle text-secondary px-2 rounded">${ct.created_at}</span></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary me-2 edit-btn" data-id="${ct.id}" data-name="${ct.class_type}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${ct.id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                });
            } else {
                tbody = '<tr><td colspan="5" class="text-center text-secondary">No class types found</td></tr>';
            }
            $('#classTypeTableBody').html(tbody);
        }

        function loadTable(){
            $.ajax({
                url: 'api.php?endpoint=class_type_get_all',
                method: 'GET',
                dataType: 'json',
                success: function(res){
                    if(res.status){
                        classType = res.data; // store all data for filtering
                        renderTable(classType);
                    } else {
                        renderTable([]);
                    }
                }
            });
        }

        // Filter on search input
        $('#searchClassType').on('input', function(){
            let query = $(this).val().toLowerCase();
            let filtered = classType.filter(ct => ct.class_type.toLowerCase().includes(query));
            renderTable(filtered);
        });

        loadTable();

        // Add
        $('#addClassTypeForm').submit(function(e){
            e.preventDefault();
            let btn = $('#btnAdd');
            let name = $('#classtype').val();
            setLoading(btn, true);

            $.ajax({
                url: 'api.php?endpoint=class_type_create',
                method: 'POST',
                data: { name: name },
                dataType: 'json',
                success: function(res){
                    setLoading(btn, false);
                    if(res.status){
                        $('#addClassTypeModal').modal('hide');
                        $('#addClassTypeForm')[0].reset();
                        showAlert(res.message);
                        loadTable();
                    } else {
                        alert(res.message);
                    }
                }
            });
        });

        // Fill update modal
        $('#classTypeTableBody').on('click', '.edit-btn', function(){
            let id = $(this).data('id');
            let name = $(this).data('name');
            $('#updateClassTypeId').val(id);
            $('#updateClassTypeName').val(name);
            $('#updateClassTypeModal').modal('show');
        });

        // Update
        $('#updateClassTypeForm').submit(function(e){
            e.preventDefault();
            let btn = $('#btnUpdate');
            let id = $('#updateClassTypeId').val();
            let name = $('#updateClassTypeName').val();
            setLoading(btn, true);

            $.ajax({
                url: 'api.php?endpoint=class_type_update',
                method: 'POST',
                data: { id: id, name: name },
                dataType: 'json',
                success: function(res){
                    setLoading(btn, false);
                    if(res.status){
                        $('#updateClassTypeModal').modal('hide');
                        showAlert(res.message);
                        loadTable();
                    } else {
                        alert(res.message);
                    }
                }
            });
        });

        // Fill delete modal
        $('#classTypeTableBody').on('click', '.delete-btn', function(){
            let id = $(this).data('id');
            $('#deleteClassTypeId').val(id);
            $('#deleteClassTypeModal').modal('show');
        });

        // Delete
        $('#deleteClassTypeForm').submit(function(e){
            e.preventDefault();
            let btn = $('#btndelete');
            let id = $('#deleteClassTypeId').val();
            setLoading(btn, true);

            $.ajax({
                url: 'api.php?endpoint=class_type_delete',
                method: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(res){
                    setLoading(btn, false);
                    if(res.status){
                        $('#deleteClassTypeModal').modal('hide');
                        showAlert(res.message);
                        loadTable();
                    } else {
                        alert(res.message);
                    }
                }
            });
        });

    });
</script>