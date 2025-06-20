<?php
// Este archivo está diseñado para ser incluido en gestion_ofertas.php
// Asume que las variables para los selectores ($modalidades, $tipos_oferta,
// $areas_conocimiento, $carreras, $estados) ya están definidas en gestion_ofertas.php.
?>

<!-- Modal para Crear/Editar Oferta -->
<div class="modal fade" id="ofertaModal" tabindex="-1" aria-labelledby="ofertaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="ofertaModalLabel">Crear Nueva Oferta</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="ofertaForm">
          <input type="hidden" id="ofertaId" name="idOferta">

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="titulo" class="form-label">Título de la Oferta <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="titulo" name="titulo" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="empresa_idEmpresa" class="form-label">Empresa <span class="text-danger">*</span></label>
              <!-- En un entorno de administración, aquí podría ir un selector de empresas.
                   Por simplicidad, y asumiendo que el admin puede seleccionar, o bien
                   se cargará automáticamente si es una oferta de una empresa específica.
                   Para esta fase, dejaremos un input de texto, se puede mejorar con un select dinámico o autocompletado. -->
              <input type="text" class="form-control" id="empresa_idEmpresa" name="empresa_idEmpresa"
                placeholder="ID de la Empresa" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required></textarea>
          </div>

          <div class="mb-3">
            <label for="requisitos" class="form-label">Requisitos <span class="text-danger">*</span></label>
            <textarea class="form-control" id="requisitos" name="requisitos" rows="3" required></textarea>
          </div>

          <div class="mb-3">
            <label for="beneficios" class="form-label">Beneficios</label>
            <textarea class="form-control" id="beneficios" name="beneficios" rows="2"></textarea>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="modalidad_id_modalidad" class="form-label">Modalidad <span
                  class="text-danger">*</span></label>
              <select class="form-select" id="modalidad_id_modalidad" name="modalidad_id_modalidad" required>
                <!-- Opciones se cargarán con JavaScript -->
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="tipo_oferta_id_tipo_oferta" class="form-label">Tipo de Oferta <span
                  class="text-danger">*</span></label>
              <select class="form-select" id="tipo_oferta_id_tipo_oferta" name="tipo_oferta_id_tipo_oferta" required>
                <!-- Opciones se cargarán con JavaScript -->
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="duracion_meses" class="form-label">Duración (meses) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="duracion_meses" name="duracion_meses" min="1" required>
            </div>
            <div class="col-md-4 mb-3">
              <label for="horario" class="form-label">Horario</label>
              <input type="text" class="form-control" id="horario" name="horario">
            </div>
            <div class="col-md-4 mb-3">
              <label for="remuneracion" class="form-label">Remuneración</label>
              <input type="text" class="form-control" id="remuneracion" name="remuneracion">
            </div>
          </div>

          <div class="mb-3">
            <label for="area_conocimiento_id_area" class="form-label">Área de Conocimiento <span
                class="text-danger">*</span></label>
            <select class="form-select" id="area_conocimiento_id_area" name="area_conocimiento_id_area" required>
              <!-- Opciones se cargarán con JavaScript -->
            </select>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="semestre_minimo" class="form-label">Semestre Mínimo</label>
              <input type="number" class="form-control" id="semestre_minimo" name="semestre_minimo" min="1" max="15">
            </div>
            <div class="col-md-4 mb-3">
              <label for="promedio_minimo" class="form-label">Promedio Mínimo</label>
              <input type="number" class="form-control" id="promedio_minimo" name="promedio_minimo" min="0.0" max="5.0"
                step="0.01">
            </div>
            <div class="col-md-4 mb-3">
              <label for="cupos_disponibles" class="form-label">Cupos Disponibles <span
                  class="text-danger">*</span></label>
              <input type="number" class="form-control" id="cupos_disponibles" name="cupos_disponibles" min="1"
                required>
            </div>
          </div>

          <div class="mb-3">
            <label for="habilidades_requeridas" class="form-label">Habilidades Requeridas</label>
            <textarea class="form-control" id="habilidades_requeridas" name="habilidades_requeridas"
              rows="2"></textarea>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
              <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
            </div>
            <div class="col-md-4 mb-3">
              <label for="fecha_fin" class="form-label">Fecha de Fin</label>
              <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
            </div>
            <div class="col-md-4 mb-3">
              <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento <span
                  class="text-danger">*</span></label>
              <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="estado_id_estado" class="form-label">Estado de la Oferta <span
                class="text-danger">*</span></label>
            <select class="form-select" id="estado_id_estado" name="estado_id_estado" required>
              <!-- Opciones se cargarán con JavaScript -->
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Carreras Dirigidas:</label>
            <div id="carrerasDirigidasContainer">
              <!-- Checkboxes de carreras se cargarán con JavaScript -->
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardarOferta">Guardar Oferta</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>