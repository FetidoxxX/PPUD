<!-- Modal para Crear/Editar Administrador -->
<div class="modal fade" id="administradorModal" tabindex="-1" aria-labelledby="administradorModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="administradorModalLabel">Crear Nuevo Administrador</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="administradorForm">
        <div class="modal-body">
          <input type="hidden" id="adminId" name="idAdministrador">

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="nombres" name="nombres" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="apellidos" name="apellidos" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="correo" class="form-label">Correo <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="correo" name="correo" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="telefono" class="form-label">Teléfono</label>
              <input type="text" class="form-control" id="telefono" name="telefono">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="n_doc" class="form-label">Número de Documento <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="n_doc" name="n_doc" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="tipo_documento_id_tipo" class="form-label">Tipo de Documento <span
                  class="text-danger">*</span></label>
              <select class="form-select" id="tipo_documento_id_tipo" name="tipo_documento_id_tipo" required>
                <!-- Opciones cargadas dinámicamente por JS -->
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="ciudad_id_ciudad" class="form-label">Ciudad</label>
              <select class="form-select" id="ciudad_id_ciudad" name="ciudad_id_ciudad">
                <!-- Opciones cargadas dinámicamente por JS -->
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="estado_id_estado" class="form-label">Estado <span class="text-danger">*</span></label>
              <select class="form-select" id="estado_id_estado" name="estado_id_estado" required>
                <!-- Opciones cargadas dinámicamente por JS -->
              </select>
            </div>
          </div>

          <!-- El campo de contraseña ha sido removido según el requisito de no permitir el cambio de contraseña desde este módulo. -->

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger" id="btnGuardarAdministrador">Guardar Administrador</button>
        </div>
      </form>
    </div>
  </div>
</div>