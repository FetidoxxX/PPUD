<div class="modal fade" id="detalleEmpresaModal" tabindex="-1" aria-labelledby="detalleEmpresaModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <!-- Tamaño más grande y centrado -->
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="detalleEmpresaModalLabel">Perfil de la Empresa</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="contenidoDetalleEmpresa">
          <!-- El contenido del perfil de la empresa se cargará aquí -->
          <p class="text-center text-muted py-5"><i class="fas fa-spinner fa-spin me-2"></i>Cargando perfil de la
            empresa...</p>
        </div>
        <hr class="my-4">
        <div class="d-flex justify-content-end mb-3">
          <!-- Botón para crear referencia. Se llenará con el ID del estudiante y empresa por JS -->
          <!-- Se añade data-company-id al formulario para pasar el ID de la empresa -->
          <form class="d-inline-block create-reference-form-estudiante" id="createReferenceFormEstudiante"
            data-company-id="" data-company-name="">
            <button type="submit" class="btn btn-info" id="btnCrearReferenciaEmpresa">
              <i class="fas fa-plus-circle me-2"></i> Crear Referencia
            </button>
          </form>
        </div>
        <div id="empresaReferenciasListContainer" class="mt-4">
          <h6><i class="fas fa-comments me-2 text-primary"></i>Referencias de Estudiantes a esta Empresa</h6>
          <!-- Las referencias de la empresa se cargarán aquí por JS -->
          <p class="text-muted text-center">Cargando referencias...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>