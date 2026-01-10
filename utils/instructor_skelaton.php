<!-- instructor_skeleton.php -->
<div class="row g-4">
    <?php for ($i = 0; $i < 6; $i++): ?>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="placeholder-glow">
                            <div class="rounded-circle bg-secondary-subtle placeholder" style="width:120px; height:120px;"></div>
                        </div>
                        <div class="ms-3 w-100">
                            <p class="placeholder-glow mb-2">
                                <span class="placeholder col-7"></span>
                            </p>
                            <p class="placeholder-glow mb-2">
                                <span class="placeholder col-6"></span>
                            </p>
                            <p class="placeholder-glow mb-2">
                                <span class="placeholder col-8"></span>
                            </p>
                            <div class="placeholder-glow mt-2">
                                <span class="placeholder col-5"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="border-top py-1 text-center">
                    <span class="placeholder col-4"></span>
                </div>
            </div>
        </div>
    <?php endfor; ?>
</div>
