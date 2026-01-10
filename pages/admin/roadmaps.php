<!-- Roadmap section -->
<section >
    <h3 class="mb-0">Roadmap Course</h3>
    <p class="text-secondary mb-3">You can create roadmap of your course</p>

    <div class="p-0 border-bottom pb-3">
        <div class="d-flex justify-content-between align-items-center">
        <!-- form search -->
        <div class="col-3">
            <form action="" class="d-flex border rounded bg-white">
            <input type="text" id="searchRoadmap" placeholder="Search Course..." class="form-control shadow-none border-0 bg-transparent">
            <button class="btn">
                <i class="bi bi-search"></i>
            </button>
            </form>
        </div>

        <!-- btn add roadmap -->
        <button class="btn btn-light border" data-bs-toggle="modal" data-bs-target="#addRoadmapModal">
            Add Roadmap +
        </button>
        </div>
    </div>

    
    <!-- Success Alert -->
    <div id="successAlert" class="alert alert-success alert-dismissible fade show mt-3 " style="display:none;" role="alert">
        <span id="successMessage"></span>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>


    <div class="p-0">
        <div class="table-responsive mt-3">
        <table class="table  table-bordered mb-0">
            <thead>
                <tr>
                    <td scope="col" class="text-secondary">#</td>
                    <td scope="col" class="text-secondary">Course</td>
                    <td scope="col" class="text-secondary ps-4">Topics</td>
                    <td scope="col" class="text-secondary">Created By</td>
                    <td scope="col" class="text-secondary">Created At</td>
                    <td scope="col" class="text-center text-secondary">Action</td>
                </tr>
            </thead>
            <tbody id="roadmapdata">
               

            <!-- <tr>
                <td colspan="6" class="text-danger text-center"><span>No roadmap</span></td>
            </tr> -->

            </tbody>
        </table>
        </div>
    </div>

    <!-- Add Roadmap Modal -->
    <div class="modal fade" id="addRoadmapModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Roadmap</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <form id="formRoadmapAdd">
                    <div class="modal-body">
                        <!-- Select Course -->
                        <div class="mb-3">
                            <label class="form-label">Select Course</label>
                            <select id="course" required class="form-select shadow-none border">
                                <option value="" selected disabled>Select Course</option>
                            </select>
                        </div>

                        <!-- Lesson Input -->
                        <div class="mb-3">
                            <label class="form-label">Lesson</label>
                            <div class="d-flex mb-2">
                                <input type="text" id="lessonInput" class="form-control shadow-none border" placeholder="Enter lesson">
                                <button type="button" id="addLessonBtn" class="btn btn-primary ms-2">Add</button>
                            </div>

                            <!-- Preview area -->
                            <div id="lessonPreview" class="d-flex flex-row flex-nowrap overflow-auto border rounded p-2" style="gap: 5px; min-height: 40px;">
                               
                            </div>
                            <p id="error" class="text-danger d-none"></p>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveRoadmapBtn">
                            Save Roadmap
                            <div class="spinner-border spinner-border-sm text-light ms-2 d-none" role="status"  id="saveRoadmapSpinner">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

   <!-- Update Roadmap Modal -->
    <div class="modal fade" id="updateRoadmapModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Roadmap</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <form id="formRoadmapUpdate">
                    <div class="modal-body">
                        <input type="hidden" id="updateRoadmapId">

                        <!-- Course -->
                        <div class="mb-3">
                            <label class="form-label">Select Course</label>
                            <select class="form-select shadow-none border" id="updateCourse">
                                <option value="" selected disabled>Select Course</option>
                            </select>
                        </div>

                        <!-- Lesson input -->
                        <div class="mb-3">
                            <label class="form-label">Lesson</label>
                            <div class="d-flex mb-2">
                                <input type="text" id="updateLessonInput" class="form-control shadow-none border" placeholder="Enter lesson">
                                <button type="button" id="updateAddLessonBtn" class="btn btn-primary ms-2">Add</button>
                            </div>

                            <!-- Preview -->
                            <div id="updateLessonPreview" class="d-flex flex-row flex-nowrap overflow-auto border rounded p-2" style="gap:5px; min-height:40px;">
                               
                            </div>
                            <div id="updateError" class="text-danger d-none mt-1"></div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="updateRoadmapBtn">
                            Update
                            <span class="spinner-border spinner-border-sm text-light ms-2 d-none" role="status" id="updateRoadmapSpinner">
                                <span class="visually-hidden">Loading...</span>
                            </span>
                        </button>
                    </div>
                </form>
        </div>
        </div>
    </div>


    <!-- Delete Roadmap Modal -->
    <div class="modal fade" id="deleteRoadmapModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Delete Roadmap</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="formDeleteModal">
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete this roadmap?</p>
                    <input type="hidden" id="deleteRoadmapId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="deleteRoadmapBtn">
                        Delete
                        <span class="spinner-border spinner-border-sm text-light ms-2 d-none" role="status" id="deleteRoadmapSpinner">
                            <span class="visually-hidden">Loading...</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>

</section>
<!-- Roadmap section -->
<script>
    $(document).ready(function() {

        let selectedCourseId = null;
        let tempLessons = [];
        let roadmapsData = [];
        let updateLessons = [];
        
        function showAlert(message) {
            $('#successMessage').text(message);
            $('#successAlert').stop(true, true).fadeIn(); // Stop previous animations
            setTimeout(function() {
                $('#successAlert').fadeOut('slow');
            }, 3000); // Each alert fades out after 3 seconds
        }

        // ===== Render preview lessons =====
        function renderPreviewLessons(){
            const container = $('#lessonPreview');
            container.empty();

            if(tempLessons.length === 0){
                // Show placeholder text
                container.append(`<span class="text-secondary">No lessons added yet</span>`);
                return;
            }

            // Render lessons
            tempLessons.forEach((l, i) => {
                container.append(`
                    <span class="bg-secondary-subtle text-secondary px-2 py-1 rounded d-inline-flex align-items-center">
                                ${l} <span class="ms-1 text-danger cursor-pointer" data-index="${i}">&times;</span>
                            </span>
                        `);
            });
        }
        renderPreviewLessons()

        // ===== Load courses into selects =====
        function loadCourses() {
            $.ajax({
                url: 'api.php?endpoint=course_getall',
                method: 'GET',
                dataType: 'json',
                success: function(res){
                    if(res.status){
                        let options = '<option value="" disabled selected>Select Course</option>';
                        res.data.forEach(c => { 
                            options += `<option value="${c.id}">${c.course}</option>`; 
                        });
                        $('#course, #updateCourse').html(options);
                    }
                }
            });
        }

        // ===== Render roadmap table =====
        function renderRoadmaps(data){
            if(!data || data.length === 0){
                $('#roadmapdata').html('<tr><td colspan="6" class="text-danger text-center">No roadmap</td></tr>');
                return;
            }

            const rows = data.map((r, i) => {
                let topics = '';
                try {
                    const lessonsArray = JSON.parse(r.lessons);
                    if (Array.isArray(lessonsArray)) {
                        topics = `<ol class="m-0 ps-5">${lessonsArray.map(l => `<li>${l}</li>`).join('')}</ol>`;
                    } else {
                        topics = `<span>${r.lessons}</span>`;
                    }
                } catch {
                    topics = `<span>${r.lessons}</span>`;
                }

                return `<tr>
                    <td>${i+1}</td>
                    <td>${r.course_name}</td>
                    <td>${topics}</td>
                    <td><span class="bg-primary-subtle text-primary px-2 rounded">${r.created_by_name}</span></td>
                    <td><span class="text-secondary bg-secondary-subtle px-2 rounded">${r.created_at}</span></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-primary me-1 btn-edit" 
                            data-id="${r.id}" 
                            data-course="${r.course_id}" 
                            data-lessons='${r.lessons}'
                            data-bs-toggle="modal" data-bs-target="#updateRoadmapModal">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button 
                            class="btn btn-sm btn-danger btn-delete" 
                            data-id="${r.id}"
                            data-bs-toggle="modal" data-bs-target="#deleteRoadmapModal"
                        >
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>`;
            }).join('');

            $('#roadmapdata').html(rows);
        }

        // ===== Load all roadmaps =====
        function loadRoadmaps() {
            $.ajax({
                url: 'api.php?endpoint=roadmap_getall',
                method: 'GET',
                dataType: 'json',
                success: function(res){
                    if(res.status){
                        roadmapsData = res.data;
                        renderRoadmaps(roadmapsData);
                    }
                }
            });
        }
        // ===== On course select =====
        $('#course').change(function(){
            selectedCourseId = $(this).val();
            tempLessons = []; // reset temp lessons when changing course
            renderPreviewLessons();
        });

        // ===== Add lesson =====
        $('#addLessonBtn').click(function(){
            let lesson = $('#lessonInput').val().trim();  
            $('#error').addClass('d-none').text('');
            $('#lessonPreview').removeClass('border-danger');

            if(lesson){
                tempLessons.push(lesson);
                $('#lessonInput').val('');

                // Hide error if any
                renderPreviewLessons();
            }
        });

        // ===== Remove lesson =====
        $(document).on('click', '#lessonPreview span span', function(){
            let index = $(this).data('index');
            tempLessons.splice(index,1);
            renderPreviewLessons();
        });

        // ===== On edit button click =====
        $(document).on('click', '.btn-edit', function () {
            const id = $(this).data('id');
            const course = $(this).data('course');
            const lessonsStr = $(this).attr('data-lessons');

            $('#updateCourse').val(course);
            $('#updateRoadmapId').val(id);

            try {
                updateLessons = JSON.parse(lessonsStr);
            } catch {
                updateLessons = [lessonsStr];
            }

            renderUpdateLessons();
        });

        $(document).on('click', '#updateLessonPreview span span', function(){
            let index = $(this).data('index');
            updateLessons.splice(index,1);
            renderUpdateLessons();
        });

        $(document).on('click','.btn-delete',function(e){
            $('#deleteRoadmapId').val($(this).data('id'));
        })
 
        // ===== render lesson to updatePrview =====
        function renderUpdateLessons(){
            const container = $('#updateLessonPreview');
            container.empty();

            if(updateLessons.length === 0){
                container.append(`<span class="text-secondary">No lessons added yet</span>`);
                return;
            }

            updateLessons.forEach((l, i) => {
                container.append(`
                    <span class="bg-secondary-subtle text-secondary px-2 py-1 rounded d-inline-flex align-items-center">
                        ${l}
                        <span class="ms-1 text-danger cursor-pointer" data-index="${i}">&times;</span>
                    </span>
                `);
            });
        }

        // ===== Add lesson in update modal =====
        $('#updateAddLessonBtn').click(function(){
            let lesson = $('#updateLessonInput').val().trim();
            $('#updateError').addClass('d-none').text('');
            $('#updateLessonPreview').removeClass('border-danger');

            if(lesson){
                updateLessons.push(lesson);   // add to updateLessons array
                $('#updateLessonInput').val(''); // clear input
                renderUpdateLessons();         // re-render preview
            }
        });

        // ===== Search =====
        $('#searchRoadmap').on('input', function(){
            const keyword = $(this).val().toLowerCase();
            const filtered = roadmapsData.filter(r =>
                r.course_name.toLowerCase().includes(keyword) ||
                r.lessons.toLowerCase().includes(keyword) ||
                r.created_by_name.toLowerCase().includes(keyword)
            );
            renderRoadmaps(filtered);
        });

        // ===== Submit roadmap =====
        $('#formRoadmapAdd').submit(function(e){
            e.preventDefault();
            if(!selectedCourseId) return alert('Please select a course');
            if(tempLessons.length === 0) {
                $('#error').text('Add at least one lesson').removeClass('d-none');
                $('#lessonPreview').addClass('border-danger'); // highlight preview 
                return
            };

            // Show spinner
            $('#saveRoadmapSpinner').removeClass('d-none');
            $('#saveRoadmapBtn').prop('disabled', true);
            
            $.ajax({
                url: 'api.php?endpoint=roadmap_create',
                method: 'POST',
                data: {
                    course_id: selectedCourseId,
                    lessons: JSON.stringify(tempLessons)
                },
                dataType: 'json',
                success: function(res){
                    if(res.status){
                        tempLessons = [];
                        renderPreviewLessons();
                        $('#lessonInput').val('');
                        $('#course').val('');
                        $('#addRoadmapModal').modal('hide');
                        showAlert(res.message);
                        loadRoadmaps(); // function to reload table
                    } else alert(res.message);
                },
                complete: function(){
                    // Hide spinner
                    $('#saveRoadmapSpinner').addClass('d-none');
                    $('#saveRoadmapBtn').prop('disabled', false);
                }
            });
        });

        $('#formRoadmapUpdate').submit(function(e){
            e.preventDefault();
            const id = $('#updateRoadmapId').val();
            const courseId = $('#updateCourse').val();

            $('#updateError').addClass('d-none').text('');
            $('#updateLessonPreview').removeClass('border-danger');

            if(!courseId){
                alert('Please select a course');
                return;
            }
            if(updateLessons.length === 0){
                $('#updateError').text('Add at least one lesson').removeClass('d-none');
                $('#updateLessonPreview').addClass('border-danger');
                return;
            }

            // Show spinner & disable button
            $('#updateRoadmapSpinner').removeClass('d-none');
            $('#updateRoadmapBtn').prop('disabled', true);

            $.ajax({
                url: 'api.php?endpoint=roadmap_update',
                method: 'POST',
                dataType: 'json',
                data: {
                    id: id,
                    course_id: courseId,
                    lessons: JSON.stringify(updateLessons)
                },
                success: function(res){
                    if(res.status){
                        $('#updateRoadmapModal').modal('hide');
                        updateLessons = [];
                        renderUpdateLessons();
                        showAlert(res.message);
                        loadRoadmaps();
                    } else {
                        alert(res.message);
                    }
                },
                complete: function(){
                    // Hide spinner & enable button
                    $('#updateRoadmapSpinner').addClass('d-none');
                    $('#updateRoadmapBtn').prop('disabled', false);
                }
            });
        });

        // ===== Delete roadmap =====
        $('#formDeleteModal').submit(function(e){
            e.preventDefault();
            const id = $('#deleteRoadmapId').val();

            const btn = $('#deleteRoadmapBtn');
            const spinner = $('#deleteRoadmapSpinner');

            // Show spinner and disable button
            btn.prop('disabled', true);
            spinner.removeClass('d-none');  

            $.ajax({
                url: 'api.php?endpoint=roadmap_delete',
                method: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(res){
                    if(res.status){
                        // Reload roadmap table
                        showAlert(res.message);
                        $('#deleteRoadmapModal').modal('hide');
                        loadRoadmaps();
                    } else {
                        alert(res.message || 'Failed to delete roadmap.');
                    }
                },
                error: function(){
                    alert('An error occurred while deleting the roadmap.');
                },
                complete: function(){
                    // Hide spinner and enable button
                    spinner.addClass('d-none');
                    btn.prop('disabled', false);
                }
            });
        });

        // ===== Initialize =====
        loadCourses();
        loadRoadmaps();

    });
</script>

