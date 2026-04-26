<div class="modal" id="infoDeleteModal" aria-hidden="true">
    <div class="modal-overlay" data-delete-close></div>
    <div class="modal-content info-delete-modal">
        <div class="modal-header">
            <div>
                <h3 id="deleteInfoTitle">Hapus Sekilas Info?</h3>
                <p class="helper-text" id="deleteInfoLabel">Anda yakin ingin menghapus item ini?</p>
            </div>
            <button type="button" class="modal-close" data-delete-close aria-label="Tutup konfirmasi"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn--ghost pill" data-delete-close>Batal</button>
            <button type="button" class="btn btn--primary pill shadow" data-delete-confirm>Hapus</button>
        </div>
    </div>
</div>

<form id="infoDeleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
