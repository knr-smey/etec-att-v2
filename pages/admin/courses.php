<!-- Course section -->
<section>
    
    <h3 class="mb-0">Course Management</h3>
    <p class="text-secondary mb-3">Manage the courses offered at your school</p>

    <!-- Top toolbar -->
    <div class=" p-0 border-bottom pb-3">
        <div class="d-flex justify-content-between align-items-center">
            <!-- form search -->
            <div class="d-flex align-items-center col-10">
                <div class="col-3 ">
                    <form class="d-flex border rounded bg-white">
                        <input id="searchInput" type="text" placeholder="Search Course..." class="form-control shadow-none border-0 bg-transparent">
                        <button class="btn">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </div>
                <div class="ms-2">
                    <select name="" id="categoryFilter" class="form-select shadow-none border">
                        <!-- <option value=""></option> -->
                    </select>
                </div>
            </div>

            <!-- btn add course -->
            <button class="btn btn-light border" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                <i class="bi bi-plus-lg me-1"></i> Add Course
            </button>
        </div>
    </div>

    <!-- Success Alert -->
    <div id="successAlert" class="alert alert-success alert-dismissible fade show mt-3 " style="display:none;" role="alert">
        <span id="successMessage"></span>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <!-- Table -->
    <div class=" p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                <tr>
                    <td class="text-secondary">#</td>
                    <td class="text-secondary">Course Name</td>
                    <!-- <td class="text-secondary">Price</td> -->
                    <td class="text-secondary">Category</td>
                    <td class="text-secondary">Created By</td>
                    <td class="text-secondary">Created At</td>
                    <td class="text-secondary">Roadmap</td>
                    <td class="text-center text-secondary">Action</td>
                </tr>
                </thead>
                <tbody id="courseTableBody">
                   <!-- data -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Course Modal -->
    <div class="modal fade" id="addCourseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Course</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <form id="addCourseForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Course Name</label>
                            <input type="text" id="course" class="form-control shadow-none border" placeholder="Enter course name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select id="courseCategory" class="form-select shadow-none border" required>
                                <option value="" disabled selected>Select category</option>
                                <!-- Categories will be loaded here dynamically -->
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="addBtn">
                            Add
                            <span class="spinner-border spinner-border-sm text-light ms-2 d-none" role="status" id="addSpinner">
                                <span class="visually-hidden">Loading...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Course Modal -->
    <div class="modal fade" id="updateCourseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Course</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>

                <form id="updateCourseForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="updateCourseId">

                        <div class="mb-3">
                            <label class="form-label">Course Name</label>
                            <input 
                                type="text" 
                                name="course"
                                class="form-control shadow-none border" 
                                id="updateCourseName" 
                                placeholder="Enter course name" 
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select 
                                name="category_id"
                                id="updateCourseCategory" 
                                class="form-select shadow-none border" 
                                required
                            >
                                <!-- Categories will be loaded dynamically -->
                            </select>
                        </div>
                    </div>

                   <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="updateBtn">
                            Update
                            <span class="spinner-border spinner-border-sm text-light ms-2 d-none" role="status" id="updateSpinner">
                                <span class="visually-hidden">Loading...</span>
                            </span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>


    <!-- Delete Course Modal -->
    <div class="modal fade" id="deleteCourseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
            <h6 class="modal-title">Delete Course</h6>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteCourseForm">
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete this course?</p>
                    <input type="hidden" id="deleteCourseId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="deleteBtn">
                        Delete
                        <span class="spinner-border spinner-border-sm text-light ms-2 d-none" role="status" id="deleteSpinner">
                            <span class="visually-hidden">Loading...</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>
</section>
<!-- Course section -->

<script>
    $(document).ready(function(){

        let course; // declare array for search and filter

        function showAlert(message) {
            $('#successMessage').text(message);
            $('#successAlert').stop(true, true).fadeIn(); // Stop previous animations
            setTimeout(function() {
                $('#successAlert').fadeOut('slow');
            }, 3000); // Each alert fades out after 3 seconds
        }

        function renderCourses(courseList) {
            const tbody = $('#courseTableBody');
            tbody.empty();
            
            if (courseList.length === 0) {
                tbody.html('<tr><td colspan="8" class="text-center text-secondary">No courses found</td></tr>');
            } else {
                let count = 0
                courseList.forEach(c => {
                    count++
                    tbody.append(`
                        <tr>
                            <td>${count}</td>
                            <td>${c.course}</td>
                            <td><span class="bg-success-subtle text-success rounded px-2">${c.category_name}</span></td>
                            <td><span class="bg-primary-subtle text-primary rounded px-2">${c.created_by_name}</span></td>
                            <td><span class="bg-secondary-subtle text-secondary rounded px-2">${c.created_at}</span></td>
                            <td><a href="#" class="text-decoration-none">View</a></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary me-1 btn-edit"
                                    data-id="${c.id}"
                                    data-course="${c.course}"
                                    data-price="${c.price}"
                                    data-categoryid="${c.category_id}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-delete"
                                    data-id="${c.id}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });
            }
        }
        
        // Assuming you have an input with id="searchInput"
        $('#searchInput').on('input', function() {
            const query = $(this).val().toLowerCase();
            
            // Filter the loaded courses array
            const filtered = course.filter(c => c.course.toLowerCase().includes(query));
            
            renderCourses(filtered);
        });

        // Filter function by category
        function filterByCategory(categoryId) {
            let filtered = course;

            if (categoryId) {
                filtered = filtered.filter(c => c.category_id == categoryId);
            }

            renderCourses(filtered);
        }

        // Event: when category changes search
        $('#categoryFilter').on('change', function() {
            const categoryId = $(this).val();
            filterByCategory(categoryId);
        });
        
        // fetch categories
        function loadCategories() {
            $.ajax({
                url: 'api.php?endpoint=category_getsome',  // <-- update with your actual endpoint URL
                type: 'GET',
                dataType: 'json',
                success: function (res) {
                    if (res.status) {
                        const categories = res.data; // expecting array of categories
            
                        // First select
                        const courseSelect = $('#courseCategory');
                        courseSelect.empty();
                        courseSelect.append('<option value="" disabled selected>Select category</option>');
                        
                        // Second select
                        const filterSelect = $('#categoryFilter');
                        filterSelect.empty();
                        filterSelect.append('<option value="" selected disabled>Filted By Category</option>');
                        filterSelect.append('<option value="" >All</option>');
                        
                        // Populate both selects
                        categories.forEach(cat => {
                            const option = `<option value="${cat.id}">${cat.category}</option>`;
                            courseSelect.append(option);
                            filterSelect.append(option);
                        });
                    } else {
                        console.error('Error loading categories: ' + res.message);
                    }
                },
                error: function (err) {
                    console.error('AJAX error:', err);
                }
            });
        }
        loadCategories()
        // Load categories when the Add Course modal opens
        $('#addCourseModal').on('show.bs.modal', loadCategories);

        
        // fetch course
        function loadCourses() {
            $.ajax({
                url: 'api.php?endpoint=course_getall',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if(res.status){
                        const tbody = $('#courseTableBody');
                        tbody.empty();
                        course = res.data;
                        if(res.data.length === 0){
                            tbody.append('<tr><td colspan="8" class="text-center text-secondary">No courses found</td></tr>');
                        } else {
                            let count = 0;
                            res.data.forEach((course, index) => {
                                count++;
                                tbody.append(`
                                    <tr>
                                        <td >${count}</td>
                                        <td>${course.course}</td>
                                        <td><span class="bg-success-subtle text-success rounded px-2">${course.category_name}</span></td>
                                        <td><span class="bg-primary-subtle text-primary rounded px-2">${course.created_by_name}</span></td>
                                        <td><span class="bg-secondary-subtle text-secondary rounded px-2">${course.created_at}</span></td>
                                        <td><a href="#" class="text-decoration-none">View</a></td>
                                        <td class="text-center">
                                            <button 
                                                class="btn btn-sm btn-primary me-1 btn-edit" 

                                                data-id="${course.id}"
                                                data-name="${course.course}"
                                                data-categoryid="${course.category_id}"

                                                >
                                                    <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger btn-delete" data-id="${course.id}"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                `);
                            });
                        }
                    } else {
                        console.error(res.message);
                    }
                },
                error: function(err){
                    console.error('AJAX error:', err);
                }
            });
        }
        loadCourses(); 

        // Set course when clicking edit button
        $(document).on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');          
            const categoryId = $(this).data('categoryid');

            // Set basic input values
            $('#updateCourseId').val(id);
            $('#updateCourseName').val(name);
            

            // Load categories and select the current one
            $.ajax({
                url: 'api.php?endpoint=category_getsome',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        const categories = res.data; 
                        const select = $('#updateCourseCategory');
                        select.empty();
                        select.append('<option value="" disabled>Select category</option>');

                        categories.forEach(cat => {
                            select.append(`<option value="${cat.id}">${cat.category}</option>`);
                        });

                        // Set selected category after loading options
                        select.val(categoryId);
                    } else {
                        console.error('Error loading categories: ' + res.message);
                    }
                },
                error: function(err) {
                    console.error('AJAX error:', err);
                }
            });

            // Show the modal
            $('#updateCourseModal').modal('show');
        });

        // Set course ID when clicking delete button
        $(document).on('click', '.btn-delete', function() {
            const courseId = $(this).data('id');
            $('#deleteCourseId').val(courseId);
            $('#deleteCourseModal').modal('show'); // Show modal
        });
        
        // --- CRUD ---
        // --- Add Course ---
        $('#addCourseForm').submit(function(e){
            e.preventDefault();

            const addBtn = $('#addCourseForm button[type="submit"]');
            const spinner = addBtn.find('.spinner-border');

            const course = $('#course').val();
            const category_id = $('#courseCategory').val();

            // Show spinner and disable button
            spinner.removeClass('d-none');
            addBtn.prop('disabled', true);

            $.ajax({
                url: 'api.php?endpoint=course_create',
                type: 'POST',
                data: {course,category_id},
                dataType: 'json',
                success: function(res){
                    if(res.status){
                        // $('#addCourseModal').modal('hide');
                        $("#addCourseForm")[0].reset();
                        showAlert(res.message);
                        loadCourses();
                        
                    } else {
                        alert(res.message); 
                    }
                },
                complete: function(){
                    // Hide spinner and enable button
                    spinner.addClass('d-none');
                    addBtn.prop('disabled', false);
                }
            });
        });

        // --- Update Course ---
        $('#updateCourseForm').submit(function(e){
            e.preventDefault(); // prevent default form submission

            const updateBtn = $('#updateBtn');
            const spinner = $('#updateSpinner');

            const formData = $(this).serialize(); // serialize normal POST data

            // Show spinner and disable button
            spinner.removeClass('d-none');
            updateBtn.prop('disabled', true);

            $('#successAlert').hide();
            $.ajax({
                url: 'api.php?endpoint=course_update',
                type: 'POST',  // normal POST
                data: formData,
                success: function(res){
                    if(res.status){
                        $('#updateCourseModal').modal('hide');
                        showAlert(res.message);
                        loadCourses(); // reload course table
                    } else {
                        alert(res.message);
                    }
                },
                error: function(err){
                    console.error('AJAX error', err);
                },
                complete: function(){
                    // Hide spinner and enable button
                    spinner.addClass('d-none');
                    updateBtn.prop('disabled', false);
                }
            });
        });

        // --- Delete Course ---
        $('#deleteCourseForm').on('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            const id = $('#deleteCourseId').val();
            const deleteBtn = $('#deleteBtn');
            const spinner = $('#deleteSpinner');

            // Show spinner and disable button
            spinner.removeClass('d-none');
            deleteBtn.prop('disabled', true);

            $('#successAlert').hide();
            $.ajax({
                url: 'api.php?endpoint=course_delete', // Your endpoint
                type: 'POST', // Use POST for normal form submission
                data: { id: id },
                success: function(res) {
                    if(res.status){                      
                        $('#deleteCourseModal').modal('hide')
                        showAlert(res.message);
                        loadCourses(); // Reload your course table
                    } else {
                        alert(res.message);
                    }
                },
                error: function(err) {
                    console.error('AJAX error:', err);
                },
                complete: function(){
                    // Hide spinner and enable button
                    spinner.addClass('d-none');
                    deleteBtn.prop('disabled', false);
                }
            });
        });
    })
</script>