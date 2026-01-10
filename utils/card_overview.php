<!-- Dashboard Cards -->
<div class="p-0 border-bottom pb-3">
  <div class="row g-4">

    <!-- Total Class -->
    <div class="col-md-3">
      <div class="card border rounded h-100 shadow-sm">
        <div class="card-body d-flex align-items-center justify-content-between px-4">
          <div>
            <h6 class="text-muted mb-1">Total Class</h6>
            <h3 id="totalClass" class="fw-medium mb-0 placeholder-glow">
              <span class="placeholder col-6"></span>
            </h3>
          </div>
          <div class="me-3 text-primary fs-1 border-start ps-3">
            <i class="bi bi-easel2-fill"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Student -->
    <div class="col-md-3">
      <div class="card border rounded h-100 shadow-sm">
        <div class="card-body d-flex align-items-center justify-content-between px-4">
          <div>
            <h6 class="text-muted mb-1">Total Student</h6>
            <h3 id="totalStudent" class="fw-medium mb-0 placeholder-glow">
              <span class="placeholder col-6"></span>
            </h3>
          </div>
          <div class="me-3 text-success fs-1 border-start ps-3">
            <i class="bi bi-people-fill"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Male Student -->
    <div class="col-md-3">
      <div class="card border rounded h-100 shadow-sm">
        <div class="card-body d-flex align-items-center justify-content-between px-4">
          <div>
            <h6 class="text-muted mb-1">Male Student</h6>
            <h3 id="totalMale" class="fw-medium mb-0 placeholder-glow">
              <span class="placeholder col-6"></span>
            </h3>
          </div>
          <div class="me-3 text-info fs-1 border-start ps-3">
            <i class="bi bi-gender-male"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Female Student -->
    <div class="col-md-3">
      <div class="card border rounded h-100 shadow-sm">
        <div class="card-body d-flex align-items-center justify-content-between px-4">
          <div>
            <h6 class="text-muted mb-1">Female Student</h6>
            <h3 id="totalFemale" class="fw-medium mb-0 placeholder-glow">
              <span class="placeholder col-6"></span>
            </h3>
          </div>
          <div class="me-3 text-danger fs-1 border-start ps-3">
            <i class="bi bi-gender-female"></i>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- jQuery + Auto Count Script -->
<script>
$(document).ready(function () {
  const instructorId = 5; // Example instructor id

  // Count up animation function
  function autoCount(elementId, endValue, duration = 1200) {
    const element = $("#" + elementId);
    let startValue = 0;
    const increment = Math.ceil(endValue / (duration / 16)); // 60fps
    const counter = setInterval(() => {
      startValue += increment;
      if (startValue >= endValue) {
        startValue = endValue;
        clearInterval(counter);
      }
      element.text(startValue);
    }, 16);
  }

  $.ajax({
    url: "api.php?endpoint=get_totals_by_instructor",
    type: "POST",
    data: { instructor_id: instructorId },
    dataType: "json",
    success: function (response) {
      if (response.status) {
        // Remove placeholders
        $(".placeholder-glow").removeClass("placeholder-glow");
        $(".placeholder").remove();

        // Start count-up animations
        autoCount("totalClass", response.data.total_class);
        autoCount("totalStudent", response.data.total_student);
        autoCount("totalMale", response.data.total_male);
        autoCount("totalFemale", response.data.total_female);
      } else {
        console.error("Error:", response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error("AJAX Error:", error);
    }
  });
});
</script>
