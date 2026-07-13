{{--
  Modal konfirmasi aksi bersama (dipakai di index & show).
  Tombol pemicu:
    - Konfirmasi biasa (approve / serah terima / terima):
        <button type="button" class="js-confirm-action"
                data-action="{{ route(...) }}"
                data-title="Judul"
                data-body="Pesan konfirmasi"
                data-confirm-label="Setujui"
                data-confirm-class="btn-success"> ... </button>
    - Penolakan (butuh alasan):
        <button type="button" class="js-reject-action"
                data-action="{{ route(...) }}"
                data-title="Judul"> ... </button>
--}}

<!-- Generic Confirm Modal -->
<div class="modal modal-blur fade" id="confirmActionModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-start">
          <div class="modal-title mb-0" id="confirmActionTitle">Konfirmasi</div>
          <div class="text-secondary small text-end ms-3" id="confirmActionNote"></div>
        </div>
        <div class="mt-3" id="confirmActionBody"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
        <form id="confirmActionForm" method="POST">
          @csrf
          <button type="submit" id="confirmActionButton" class="btn btn-success">Ya</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Reject Modal (dengan alasan) -->
<div class="modal modal-blur fade" id="rejectActionModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
      <form id="rejectActionForm" method="POST">
        @csrf
        <div class="modal-body">
          <div class="modal-title" id="rejectActionTitle">Konfirmasi Penolakan</div>
          <div class="mt-2">
            <div class="mb-3">
              <label class="form-label required">Alasan Penolakan</label>
              <input type="text" class="form-control" name="rejection_reason" placeholder="Masukkan alasan penolakan" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger">Tolak</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
  // Modal dibuka via Bootstrap data-api (data-bs-toggle) supaya tidak butuh
  // referensi global `bootstrap` (bundel Tabler tidak selalu mengekspornya).
  document.addEventListener('DOMContentLoaded', function () {
    const confirmForm = document.getElementById('confirmActionForm');
    const confirmTitle = document.getElementById('confirmActionTitle');
    const confirmNote = document.getElementById('confirmActionNote');
    const confirmBody = document.getElementById('confirmActionBody');
    const confirmBtn = document.getElementById('confirmActionButton');

    document.querySelectorAll('.js-confirm-action').forEach(function (btn) {
      btn.setAttribute('data-bs-toggle', 'modal');
      btn.setAttribute('data-bs-target', '#confirmActionModal');
      btn.addEventListener('click', function () {
        confirmForm.setAttribute('action', btn.dataset.action);
        confirmTitle.textContent = btn.dataset.title || 'Konfirmasi';
        confirmNote.textContent = btn.dataset.note || '';
        confirmBody.innerHTML = btn.dataset.body || 'Apakah Anda yakin?';
        confirmBtn.textContent = btn.dataset.confirmLabel || 'Ya';
        confirmBtn.className = 'btn ' + (btn.dataset.confirmClass || 'btn-success');
      });
    });

    const rejectForm = document.getElementById('rejectActionForm');
    const rejectTitle = document.getElementById('rejectActionTitle');

    document.querySelectorAll('.js-reject-action').forEach(function (btn) {
      btn.setAttribute('data-bs-toggle', 'modal');
      btn.setAttribute('data-bs-target', '#rejectActionModal');
      btn.addEventListener('click', function () {
        rejectForm.setAttribute('action', btn.dataset.action);
        rejectTitle.textContent = btn.dataset.title || 'Konfirmasi Penolakan';
        rejectForm.reset();
      });
    });
  });
</script>
@endpush
