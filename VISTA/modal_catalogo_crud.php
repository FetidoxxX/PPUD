<!-- Modal para Crear/Editar elementos de Catálogo -->
<div class="modal fade" id="catalogoModal" tabindex="-1" aria-labelledby="catalogoModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="catalogoModalLabel">Crear Nuevo Elemento</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="catalogoForm">
        <div class="modal-body">
          <!-- Estos campos ocultos siempre deben estar presentes para el control -->
          <input type="hidden" id="idElemento" name="id">
          <input type="hidden" id="nombreTablaElemento" name="nombreTabla">

          <!-- Los campos del formulario se generarán dinámicamente aquí por JavaScript -->
          <!-- El campo de nombre se generará dinámicamente, pero se deja un placeholder para el feedback inicial -->
          <div class="mb-3 dynamic-field-group">
            <label for="nombreElemento" class="form-label">Nombre <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nombreElemento" name="nombre" required>
            <div class="invalid-feedback" id="retroalimentacionNombreElemento"></div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-dark" id="btnGuardarElemento">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>