// Funciones existentes de validación
function limpiar() {
  document.form.reset();
  document.form.cod.focus();
}

function validar() {
  var form = document.form;
  if (form.cod.value == 0) {
    Swal.fire({
      icon: 'error',
      title: 'ERROR!!',
      text: 'Debe digitar el codigo',
    });
    form.cod.value = '';
    form.cod.focus();
    return false;
  }
  if (form.nom.value == 0) {
    Swal.fire({
      icon: 'error',
      title: 'ERROR!!',
      text: 'Debe digitar el Nombre',
    });
    form.nom.value = '';
    form.nom.focus();
    return false;
  }
  if (form.ape.value == 0) {
    Swal.fire({
      icon: 'error',
      title: 'ERROR!!',
      text: 'Debe digitar el Apellido',
    });
    form.ape.value = '';
    form.ape.focus();
    return false;
  }
  if (form.em.value == 0) {
    Swal.fire({
      icon: 'error',
      title: 'ERROR!!',
      text: 'Debe digitar el EMAIL',
    });
    form.em.value = '';
    form.em.focus();
    return false;
  }
  if (form.tel.value == 0) {
    Swal.fire({
      icon: 'error',
      title: 'ERROR!!',
      text: 'Debe digitar el Telefono',
    });
    form.tel.value = '';
    form.tel.focus();
    return false;
  }
  if (form.fen.value == 0) {
    Swal.fire({
      icon: 'error',
      title: 'ERROR!!',
      text: 'Debe Seleccionar la Fecha de Nacimiento',
    });
    form.fen.value = '';
    form.fen.focus();
    return false;
  }
  form.submit();
}

// Función eliminar (revisada para usar SweetAlert2 como se solicitó)
function eliminar(url) {
  Swal.fire({
    title: '¿Estás seguro?',
    text: '¡No podrás revertir esto!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
  }).then((result) => {
    if (result.isConfirmed) {
      window.location = url; // Si confirma, procede con la redirección
    }
  });
}
