<div class="map-modal" id="mapEmbedModal" aria-hidden="true">
    <div class="map-modal__overlay" data-map-modal-close></div>
    <div class="map-modal__dialog" role="dialog" aria-modal="true">
        <div class="map-modal__header">
            <h3 class="pi-block__title" style="margin:0;"><i class="fas fa-link"></i> Isi Embed URL Peta</h3>
            <button type="button" class="map-modal__close" data-map-modal-close aria-label="Tutup modal"><i class="fas fa-times"></i></button>
        </div>
        <form id="mapEmbedForm">
            <div class="map-modal__body">
                <label class="pi-field full">
                    <span>Embed URL Peta (iframe)</span>
                    <textarea id="mapEmbedField" placeholder="Tempel embed dari Google Maps"></textarea>
                </label>
            </div>
            <div class="map-modal__actions">
                <button type="button" class="pi-btn" data-variant="hours" data-map-modal-close>Batalkan</button>
                <button type="submit" class="pi-btn" data-variant="map"><i class="fas fa-check"></i><span>Simpan Embed</span></button>
            </div>
        </form>
    </div>
</div>
