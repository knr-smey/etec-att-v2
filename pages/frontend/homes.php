<section class="text-center border pt-2 rounded shadow pb-4">
  <div class="p-0">
    <p class="text-secondary">ETEC CENTER</p>
    <!-- Auto typing text -->
    <h2 class="text-etec-color fw-bold mb-3 mt-3">
      <span id="typed-text"></span>
    </h2>

    <p class="mb-5 text-secondary px-5">
      ETEC Center is a leading IT training center, established to provide high-quality education in web development, mobile apps, and modern technology skills. Since its founding, it has helped students and professionals gain practical knowledge and hands-on experience in the IT industry.
    </p>

    <div class="row justify-content-center mx-4">

      <!-- Director -->
      <div class="col-md-4 d-flex p-0 border">     
        <div class="card flex-fill text-center bg-secondary-subtle border-0 rounded-0">
          <img src="./assets/lokru.png" class="object-fit-contain" height="430px" style="background-color: #BFCACC;">  
          <div class="card-body bg-light h-50 d-grid align-content-center">
            <i class="bi bi-arrow-down-circle mb-3 fs-2"></i>
            <h5 class="card-title fw-bold">HENG PHEAKNA</h5>
            <p class="text-danger fw-medium mb-2">Director</p>
            <p class="fst-italic small text-dark">
              Lok Kru: Heng Pheakna has been leading the ETEC Center since its establishment in 2017. With extensive experience in major IT fields, he has successfully overseen numerous projects and guided the center in providing high-quality education and training. His dedication and expertise have helped shape the careers of countless students and professionals in the technology sector.
            </p>
            <div class="d-flex justify-content-center gap-2">
              <a href="https://web.facebook.com/rpjor0jewb" class="text-dark fs-5"><i class="bi bi-facebook"></i></a>
              <a href="https://t.me/kroitchetlaor" class="text-dark fs-5"><i class="bi bi-telegram"></i></a>
            </div>
          </div> 
        </div>
      </div>

      <!-- Vice Director -->
      <div class="col-md-4 d-flex p-0 border">
        <div class="card flex-fill text-center bg-secondary-subtle border-0 rounded-0">          
          <div class="card-body bg-light h-50 d-grid align-content-center">
            <h5 class="card-title fw-bold text-uppercase mt-3">Kung Norasmey</h5>
            <p class="text-danger fw-medium mb-2">Vice Director</p>
            <p class="fst-italic small text-dark">
              As Vice Director of ETEC Center, Kung Norasmey has played a pivotal role in supporting Heng Pheakna since the center’s founding in 2023. With deep expertise in IT systems, he has led the creation, implementation, and maintenance of numerous advanced technology systems. His dedication to innovation and operational excellence ensures that the center delivers high-quality education while maintaining robust and efficient IT infrastructures for both staff and students.
            </p>
            <div class="d-flex justify-content-center gap-2">
              <a href="https://web.facebook.com/rak.smey.0010" class="text-dark fs-5"><i class="bi bi-facebook"></i></a>
              <a href="https://www.linkedin.com/in/rak-smey-4a93302a4/" class="text-dark fs-5"><i class="bi bi-linkedin"></i></a>
            </div>
            <i class="bi bi-arrow-down-circle my-3 fs-2"></i>
          </div> 
          <img src="./assets/mimg.png" class="object-fit-cover" height="430px"> 
        </div>
      </div>

      <!-- Web Developer -->
      <div class="col-md-4 d-flex p-0 border">
        <div class="card flex-fill text-center bg-secondary-subtle border-0 rounded-0">     
          <img src="./assets/nalen.png" class="object-fit-cover" height="430px"> 
          <div class="card-body bg-light h-50 d-grid align-content-center">
            <i class="bi bi-arrow-down-circle mb-3 fs-2"></i>
            <h5 class="card-title fw-bold text-uppercase">Srin Nalen</h5>
            <p class="text-danger fw-medium mb-2">Web Developer</p>
            <p class="fst-italic small text-dark">
              Srin Nalen is a talented Web Developer at ETEC Center, specializing in creating responsive and user-friendly websites. He is experienced in front-end and back-end technologies, ensuring smooth functionality, modern design, and optimized performance. His work helps deliver an excellent online experience for students, staff, and visitors alike.
            </p>
            <div class="d-flex justify-content-center gap-2">
              <a href="https://web.facebook.com/srinnalen" class="text-dark fs-5"><i class="bi bi-facebook"></i></a>
              <a href="https://www.linkedin.com/in/nalen-srin-068015327/" class="text-dark fs-5"><i class="bi bi-linkedin"></i></a>
            </div>
          </div> 
        </div>
      </div>

    </div>

    <!-- Footer -->
    <footer class="text-center py-3 mt-4">
      <p class="mb-0 text-secondary small">
        © 2025 ETEC Center | Created by <span class="text-danger fw-semibold">KUNG Norasmey</span>
      </p>
    </footer>
  </div>
</section>



<script>
  $(document).ready(function() {
    new Typed("#typed-text", {
      strings: [
        "Welcome to ETEC Center",
        "Build Your IT skill",
      ],
      typeSpeed: 70,     // typing speed
      backSpeed: 40,     // erasing speed
      backDelay: 1500,   // delay before backspacing
      startDelay: 500,   // delay before typing starts
      loop: true          // keep looping
    });
  });
</script>
