@extends('layouts_.vertical', ['page_title' => 'Survey/Voting'])

@section('css')
    <style>
        .nav-link.active {
            background-color: #ab2f2b !important; /* Merah saat aktif */
            color: white !important;
            border-radius: 0.375rem;
        }

        .nav-link {
            background-color: white !important;
            color: #212529; /* warna teks standar Bootstrap */
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .nav-link.active small {
            color: white !important;
        }
    </style>
@endsection

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>Total Participant : <span class="text-danger">45</span> / 90</h5>
        <div class="d-flex gap-2">
            <select class="form-select" style="width: 150px;">
                <option>All Status</option>
                <option>Submitted</option>
                <option>Not Yet</option>
            </select>
            <input type="text" class="form-control" placeholder="Search..." />
            <button class="btn btn-danger">Search</button>
        </div>
    </div>
</div>
<div class="row">
    <!-- Sidebar Tabs -->
    <div class="col-md-4">
      <div class="nav flex-column nav-pills gap-2" id="v-tabs" role="tablist" aria-orientation="vertical">
        <button class="nav-link active text-start border rounded p-3" id="tab1-tab" data-bs-toggle="pill" data-bs-target="#tab1" type="button" role="tab">
          <div class="d-flex justify-content-between">
            <strong>Metta Saputra</strong>
            <span class="badge bg-success">Submitted</span>
          </div>
          <small class="text-muted">14 Apr 2024 09:47 AM</small><br>
          <small class="text-muted">KPN Corporation | UI/UX Designer</small>
        </button>
        <button class="nav-link text-start border rounded p-3" id="tab2-tab" data-bs-toggle="pill" data-bs-target="#tab2" type="button" role="tab">
          <div class="d-flex justify-content-between">
            <strong>Jeli Farida</strong>
            <span class="badge bg-success">Submitted</span>
          </div>
          <small class="text-muted">14 Apr 2024 01:59 PM</small><br>
          <small class="text-muted">KPN Corporation | Organization Development</small>
        </button>
        <button class="nav-link text-start border rounded p-3" id="tab3-tab" data-bs-toggle="pill" data-bs-target="#tab3" type="button" role="tab">
          <div class="d-flex justify-content-between">
            <strong>Alfian Nur Fatah Azis</strong>
            <span class="badge bg-danger">Not Yet</span>
          </div>
          <small class="text-muted">KPN Corporation | HCIS Developer</small>
        </button>
      </div>
    </div>
  
    <!-- Tab Content -->
    <div class="col-md-8">
      <div class="tab-content" id="v-tabsContent">
        <div class="tab-pane fade show active" id="tab1" role="tabpanel">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Metta Saputra</h5>
              <small class="text-muted">Corporation | UI/UX Designer</small>
              <p><strong>Apakah puas dengan acara ini?</strong><br>Biasa saja</p>
              <p><strong>Bagian mana dari acara yang paling kamu suka?</strong><br>Makanan/Minuman</p>
              <p><strong>Apakah ada hal lain yang kamu suka dari acara ini?</strong><br>Panitianya asik-asik</p>
              <p><strong>Ada saran atau masukan untuk acara selanjutnya?</strong><br>Durasi acaranya lebih lama biar asik</p>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="tab2" role="tabpanel">
          <!-- Isi konten Jeli Farida -->
        </div>
        <div class="tab-pane fade" id="tab3" role="tabpanel">
          <!-- Isi konten Alfian -->
        </div>
      </div>
    </div>
  </div>
@endsection