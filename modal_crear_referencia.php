<?php
// Este archivo está diseñado para ser incluido en gestion_ofertas_empresa.php
// No necesita manejo de sesión aquí, ya que se asume que gestion_ofertas_empresa.php ya lo maneja.

// Las variables como $tipos_referencia deben ser definidas en el archivo PHP
// que incluye este modal (ej. gestion_ofertas_empresa.php)
?>

<div class="modal fade" id="crearReferenciaModal" tabindex="-1" aria-labelledby="crearReferenciaModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-lg shadow-lg">
      <div class="modal-header bg-success text-white rounded-top-lg">
        <h5 class="modal-title" id="crearReferenciaModalLabel">
          <i class="fas fa-star me-2"></i> Crear Referencia para <span id="referenciaEstudianteNombre"
            class="fw-bold"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body p-4">
        <form id="referenciaForm">
          <input type="hidden" id="referenciaEstudianteId" name="estudiante_idEstudiante">

          <input type="hidden" id="referenciaTipo" name="tipo_referencia_id_tipo_referencia" value="2">

          <div class="mb-3">
            <label for="referenciaComentario" class="form-label text-primary fw-semibold">Comentario <span
                class="text-danger">*</span></label>
            <textarea class="form-control rounded-md" id="referenciaComentario" name="comentario" rows="5"
              placeholder="Ingrese su comentario sobre el estudiante..." required></textarea>
          </div>

          <div class="mb-3">
            <label for="referenciaPuntuacion" class="form-label text-primary fw-semibold">Puntuación (0.0 - 5.0)</label>
            <input type="number" class="form-control rounded-md" id="referenciaPuntuacion" name="puntuacion" min="0.0"
              max="5.0" step="0.1" placeholder="Ej. 4.5">
          </div>

          <div class="modal-footer px-0 pb-0 pt-4 border-top-0">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
              <i class="fas fa-times me-2"></i> Cerrar
            </button>
            <button type="submit" class="btn btn-success rounded-pill px-4">
              <i class="fas fa-save me-2"></i> Guardar Referencia
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>