<div class="modal fade" id="estudianteEmpresaReferenciaModal" tabindex="-1"
  aria-labelledby="estudianteEmpresaReferenciaModalLabel" aria-hidden="true" style="z-index: 1055;">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <!-- Color primario para el modal de acción -->
        <h5 class="modal-title" id="estudianteEmpresaReferenciaModalLabel">Crear Nueva Referencia</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="estudianteEmpresaReferenciaForm">
          <input type="hidden" id="estudianteEmpresaReferenciaEstudianteId" name="estudiante_idEstudiante"
            value="<?php echo htmlspecialchars($_SESSION['usuario_id']); ?>">
          <input type="hidden" id="estudianteEmpresaReferenciaEmpresaId" name="empresa_idEmpresa">
          <input type="hidden" id="estudianteEmpresaCurrentEditingReferenceId" name="idReferencia">
          <!-- Para edición -->
          <input type="hidden" id="estudianteEmpresaTipoReferencia" name="tipo_referencia_id_tipo_referencia" value="1">
          <!-- El tipo de referencia se asume "estudiante_a_empresa" (ID 1) y no se muestra -->

          <div class="mb-3">
            <label for="estudianteEmpresaPuntuacion" class="form-label">Puntuación (1-5, opcional)</label>
            <input type="number" class="form-control" id="estudianteEmpresaPuntuacion" name="puntuacion" min="1" max="5"
              step="0.1">
          </div>

          <div class="mb-3">
            <label for="estudianteEmpresaComentario" class="form-label">Comentario</label>
            <textarea class="form-control" id="estudianteEmpresaComentario" name="comentario" rows="5"
              required></textarea>
          </div>

          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-info" id="estudianteEmpresaSaveReferenceBtn"><i
                class="fas fa-save me-2"></i>Guardar Referencia</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>