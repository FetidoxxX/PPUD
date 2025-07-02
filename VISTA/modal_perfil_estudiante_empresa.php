<div class="modal fade" id="perfilEstudianteModal" tabindex="-1" aria-labelledby="perfilEstudianteModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="perfilEstudianteModalLabel">Perfil del Estudiante</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="perfilEstudianteContent">
          <!-- El contenido del perfil del estudiante se cargará aquí -->
        </div>
        <hr class="my-4">
        <div class="d-flex justify-content-end mb-3">
          <!-- Botón para crear referencia. Se llenará con el ID del estudiante por JS -->
          <button type="button" class="btn btn-info" id="btnCrearReferenciaEstudiante">
            <i class="fas fa-plus-circle me-2"></i> Crear Referencia
          </button>
        </div>
        <div id="referenciasEstudianteContent" class="mt-4">
          <h6><i class="fas fa-comments me-2 text-primary"></i>Referencias del Estudiante</h6>
          <div id="referenciasListContainer">
            <!-- Las referencias del estudiante se cargarán aquí por JS -->
            <p class="text-muted text-center">Cargando referencias...</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>