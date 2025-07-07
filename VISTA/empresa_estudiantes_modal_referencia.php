<div class="modal fade" id="empresaEstudiantesReferenciaModal" tabindex="-1"
  aria-labelledby="empresaEstudiantesReferenciaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <!-- Cambiado a bg-info para concordar con el módulo -->
        <h5 class="modal-title" id="empresaEstudiantesReferenciaModalLabel">Crear Nueva Referencia</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="empresaEstudiantesReferenciaForm">
          <input type="hidden" id="empresaEstudiantesReferenciaEstudianteId" name="estudiante_idEstudiante">
          <input type="hidden" id="empresaEstudiantesReferenciaEmpresaId" name="empresa_idEmpresa"
            value="<?php echo htmlspecialchars($_SESSION['usuario_id']); ?>">
          <input type="hidden" id="empresaEstudiantesCurrentEditingReferenceId" name="idReferencia">
          <!-- Para edición -->

          <div class="mb-3">
            <label for="empresaEstudiantesPuntuacion" class="form-label">Puntuación (1-5, opcional)</label>
            <input type="number" class="form-control" id="empresaEstudiantesPuntuacion" name="puntuacion" min="1"
              max="5" step="0.1">
          </div>

          <div class="mb-3">
            <label for="empresaEstudiantesComentario" class="form-label">Comentario</label>
            <textarea class="form-control" id="empresaEstudiantesComentario" name="comentario" rows="5"
              required></textarea>
          </div>

          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-info" id="empresaEstudiantesSaveReferenceBtn"><i
                class="fas fa-save me-2"></i>Guardar Referencia</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>