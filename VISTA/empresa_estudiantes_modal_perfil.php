<div class="modal fade" id="empresaEstudiantesPerfilModal" tabindex="-1"
  aria-labelledby="empresaEstudiantesPerfilModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <!-- Añadido modal-dialog-centered para centrar -->
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <!-- Cambiado a bg-info -->
        <h5 class="modal-title" id="empresaEstudiantesPerfilModalLabel">Perfil del Estudiante</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="empresaEstudiantesPerfilContent">
          <!-- El contenido del perfil del estudiante se cargará aquí -->
          <!-- El botón de descarga de hoja de vida se insertará directamente aquí por JS -->
        </div>
        <hr class="my-4">
        <div class="d-flex justify-content-end mb-3">
          <!-- Botón para crear referencia. Se llenará con el ID del estudiante por JS -->
          <form class="d-inline-block create-reference-form-modulo" id="createReferenceFormModulo" data-student-id=""
            data-student-name="">
            <button type="submit" class="btn btn-info" id="btnCrearReferenciaEstudianteModulo">
              <i class="fas fa-plus-circle me-2"></i> Crear Referencia
            </button>
          </form>
        </div>
        <div id="empresaEstudiantesReferenciasContent" class="mt-4">
          <h6><i class="fas fa-comments me-2 text-primary"></i>Referencias del Estudiante</h6>
          <div id="empresaEstudiantesReferenciasListContainer">
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