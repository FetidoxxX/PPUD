<?php
// Este archivo está diseñado para ser incluido en una página que ya maneja la sesión.
?>

<div class="modal fade" id="editarReferenciaModal" tabindex="-1" aria-labelledby="editarReferenciaModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-lg shadow-lg">
      <div class="modal-header bg-warning text-dark rounded-top-lg">
        <h5 class="modal-title" id="editarReferenciaModalLabel">
          <i class="fas fa-edit me-2"></i> Editar Referencia para <span id="editReferenciaEstudianteNombre"
            class="fw-bold"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body p-4">
        <form id="editReferenciaForm">
          <input type="hidden" id="editReferenciaId" name="idReferencia">
          <input type="hidden" id="editReferenciaTipo" name="tipo_referencia_id_tipo_referencia" value="2">
          <div class="mb-3">
            <label for="editReferenciaComentario" class="form-label text-primary fw-semibold">Comentario <span
                class="text-danger">*</span></label>
            <textarea class="form-control rounded-md" id="editReferenciaComentario" name="comentario" rows="5"
              placeholder="Ingrese su comentario sobre el estudiante..." required></textarea>
          </div>

          <div class="mb-3">
            <label for="editReferenciaPuntuacion" class="form-label text-primary fw-semibold">Puntuación (0.0 -
              5.0)</label>
            <input type="number" class="form-control rounded-md" id="editReferenciaPuntuacion" name="puntuacion"
              min="0.0" max="5.0" step="0.1" placeholder="Ej. 4.5">
          </div>

          <div class="modal-footer px-0 pb-0 pt-4 border-top-0">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
              <i class="fas fa-times me-2"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-warning rounded-pill px-4">
              <i class="fas fa-save me-2"></i> Guardar Cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>