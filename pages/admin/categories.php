<!-- Category section -->
<section>
    <h3 class="mb-0">Manage Course Category </h3>
    <p class="text-secondary mb-3">You can create category of your course</p>

    <div class=" p-0 pb-3">
        <div class="d-flex justify-content-between align-items-center">                 
            <!-- form search -->
            <div class="col-3">
                <form id="categorySearchForm" class="d-flex border rounded bg-white">
                    <input id="categorySearch" type="text" placeholder="Search Category..." class="form-control shadow-none border-0 bg-transparent">
                    <button class="btn border-0">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
            
            <!-- btn add category -->
            <button tabindex="-1" class="btn btn-light border shadow-none" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                Add Category +
            </button>         
        </div>
                    
    </div>


    <div class=" p-0">

        <div class="table-responsive">
            <!-- alert success -->
            <div id="successAlert" class="alert alert-success alert-dismissible fade show" style="display:none;" role="alert">
                <span id="successMessage"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <!-- alert success -->
       

            <table class="table table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <td scope="col" class="text-secondary">#</td>
                        <td scope="col" class="text-secondary">Course Category</td>
                        <td scope="col" class="text-secondary">Created By</td>
                        <td scope="col" class="text-secondary">Created At</td>
                        <td scope="col" class="text-center text-secondary">Action</td>
                    </tr>
                </thead>
                <tbody id="categoryTableBody">
                   <!-- Category-Data -->        
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Category</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <form id="addCategory">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input required id="categoryName" type="text" class="form-control shadow-none border" placeholder="Enter category name">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="addCategoryBtn">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Category Modal -->
    <div class="modal fade" id="updateCategoryModal" >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Category</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <form id="updateCategory">
                    <div class="modal-body">
                        <input type="hidden" id="updateCategoryId">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input required type="text" class="form-control shadow-none border" id="updateCategoryName" placeholder="Enter category name">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="updateCategoryBtn">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Category Modal -->
    <div class="modal fade" id="deleteCategoryModal" >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Delete Category</h6>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <form id="deleteCategory">
                    <div class="modal-body">
                        <p class="mb-0">Are you sure you want to delete this category?</p>
                        <input type="hidden" id="deleteCategoryId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger" id="deleteCategoryBtn">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


</section>
<!-- Category section -->
<script>
    $(document).ready(function(){
        
        // array for category
        let allCategories;

        // render row category 
        function renderCategories(categories) {
            let tbody = '';
            
            if (categories.length > 0) {
                let count = 0;
                categories.forEach(function(category) {
                    count++;
                    tbody += `
                        <tr>
                            <td>${count}</td>
                            <td>${category.category}</td>
                            <td><span class="text-primary bg-primary-subtle px-2 rounded">${category.created_by}</span></td>
                            <td><span class="text-secondary bg-secondary-subtle px-2 rounded">${category.created_at}</span></td>
                            <td class="text-center">
                                <button 
                                    class="btn btn-sm btn-primary me-1 editCategoryBtn"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#updateCategoryModal"
                                    data-id="${category.id}"
                                    data-name="${category.category}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button 
                                    class="btn btn-sm btn-danger deleteCategoryBtn"
                                    data-id="${category.id}"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteCategoryModal">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                tbody = `
                    <tr>
                        <td colspan="5" class="text-center text-danger">No Category Found</td>
                    </tr>
                `;
            }

            $('#categoryTableBody').html(tbody);
        }

        function showAlert(message) {
            $('#successMessage').text(message);
            $('#successAlert').stop(true, true).fadeIn(); // Stop previous animations
            setTimeout(function() {
                $('#successAlert').fadeOut('slow');
            }, 3000); // Each alert fades out after 3 seconds
        }

        // get all category function
        function loadCategories() {
            $.ajax({
                url: 'api.php?endpoint=category_get_all',
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        let tbody = '';
                        allCategories = res.data; 
                        renderCategories(allCategories);
                    } else {
                        alert('Failed to load categories: ' + res.message);
                    }

                },
                error: function(xhr, status, error) {
                    console.error(error);
                    alert('Something went wrong while loading categories.');
                }
            });
        }

        // filter and find category
        function filterCategories() {
            let query = $('#categorySearch').val().toLowerCase().trim();
            let filtered = allCategories.filter(cat => cat.category.toLowerCase().includes(query));
            
            if (filtered.length > 0) {
                renderCategories(filtered);
            } else {
                $('#categoryTableBody').html(`
                    <tr>
                        <td colspan="5" class="text-center text-danger">No Category Found</td>
                    </tr>
                `);
            }
        }

        // Live search on keyup
        $('#categorySearch').on('keyup', filterCategories); 

        // Search on form submit (press Enter)
        $('#categorySearchForm').on('submit', function(e) {
            e.preventDefault(); // prevent page reload
            filterCategories();
        });
        
        // call function
        loadCategories();
        
        // get data to form update
        $(document).on('click','.editCategoryBtn',function(){
            $('#updateCategoryName').val($(this).data('name'))
            $('#updateCategoryId').val($(this).data('id'))
        })

        // get data to form delete
        $(document).on('click','.deleteCategoryBtn',function(){
            $('#deleteCategoryId').val($(this).data('id'))
        })
        
        // -- add feature
        $('#addCategory').on('submit',function(e){
            e.preventDefault();

            let category = $('#categoryName').val().trim();

            let $btn = $('#addCategoryBtn');
            $btn.prop('disabled', true);
            $btn.html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...`);

            $.ajax({
                url: 'api.php?endpoint=category_create', // use your endpoint
                method: 'POST',
                data: { 
                    category: category 
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        // alert('Category added successfully');
                        $('#addCategoryModal').modal('hide');
                        $('#categoryName').val('');
                        showAlert(res.message);
                        loadCategories();
                        // Optional: refresh your category list here
                    } else {
                        console.error('Error: ' + res.message);
                    }
                    
                },
                error: function(xhr, status, error) {
                    console.error(error);
                },
                complete: function() {
                    // Revert button text and enable
                    $btn.prop('disabled', false);
                    $btn.html('Add');
                }
            });

        })
        
        // -- update feature
        $('#updateCategory').on('submit', function(e) {
            e.preventDefault();

            let id = $('#updateCategoryId').val();
            let category = $('#updateCategoryName').val().trim();

            let $btn = $('#updateCategoryBtn');

            // Show spinner
            $btn.prop('disabled', true);
            $btn.html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...`);


            $.ajax({
                url: 'api.php?endpoint=category_update', // your update endpoint
                method: 'POST',
                data: {
                    id: id,
                    category: category
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        // alert('Category updated successfully!');
                        $('#updateCategoryModal').modal('hide');
                        $('#updateCategory')[0].reset();
                        showAlert(res.message);
                        loadCategories(); // Refresh category table
                    } else {
                        console.error('Update failed: ' + res.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                },
                complete: function() {
                    // Revert button text and enable
                    $btn.prop('disabled', false);
                    $btn.html('Update');
                }
            });
        });
        
        // -- add feature
        $('#deleteCategory').on('submit', function(e) {
            e.preventDefault();

            let id = $('#deleteCategoryId').val().trim();
            let $btn = $('#deleteCategoryBtn');

            // Show spinner
            $btn.prop('disabled', true);
            $btn.html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...`);


            $.ajax({
                url: 'api.php?endpoint=category_delete', // delete endpoint
                method: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        $('#deleteCategoryModal').modal('hide');
                        showAlert(res.message);
                        loadCategories(); // Refresh category table
                    } else {
                        console.error('Delete failed: ' + res.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                },
                complete: function() {
                    // Revert button text and enable
                    $btn.prop('disabled', false);
                    $btn.html('Delete');
                }
            });
        });


    })
</script>