<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/css/homeIndex.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/rowreorder/1.5.0/css/rowReorder.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <link rel="stylesheet" href="/public/css/all.css">

    <title>Agenda contactos</title>
</head>

<body>
    <div>
        <nav class="nav-menu-horizontal">
            <ul>
                <li>
                    <h4 class="title-menu-horizontal">
                        Agenda contactos
                    </h4>
                </li>
            </ul>
            <!-- <ul>
                <li>
                    Inicio
                </li>
                <li>
                    Nuevo contacto
                </li>
            </ul> -->
        </nav>

        <div>

        </div>
    </div>

    <div class="content">

        <button data-action="create-contact" type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalContent">
            Crear contacto
        </button>

        <!-- Modal -->
        <div class="modal fade modal-class" id="modalContent" tabindex="-1" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form id="formModal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitle">Modal title</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" id="modalBody">

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <table id="contactos" class="table table-striped table-bordered" style="width:100%">

        </table>

    </div>
</body>

</html>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/public/js/config.js"></script>
<script src="/public/js/functionGeneral.js"></script>
<script src="/public/js/forms.js"></script>
<script>
    $('#contactos').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "http://localhost:8000/contactos/listar",
            "type": "GET",
            "dataSrc": "data",
            "error": function(xhr, error, thrown) {
                alert("Error al cargar los datos");
            }
        },
        "columns": [{
                "data": "id",
                "title": "ID"
            },
            {
                "data": "imagen",
                "render": function(row, data, type) {
                    return "<div class='content-img-contacto'><img class='table-img-contacto' src ='" + row + "' /></div>"
                },
                "title": "Imagen"
            },
            {
                "data": "nombre",
                "title": "Nombre"
            },
            {
                "data": "telefono",
                "title": "Teléfono"
            },
            {
                "data": "edad",
                "title": "Edad"
            },
            {
                "data": "descripcion",
                "title": "Descripción"
            },
            {
                "data": null,
                "render": function(row, type, data) {
                    var ret = "";

                    ret += '<button type=\"button\" data-target=\"#modalContent\" class=\"btn btn-success me-1 mb-1\"  data-toggle=\"modal\" title=\"Editar Contacto\" data-bs-toggle=\"modal\" data-id=\"' + row.id + '\" data-action=\"edit-contacto\"><i class=\"far fa-edit fa-sm\"></i></button>\
                        ';

                    ret += '<a href=\"#modalContent \"  class=\"button-delete btn btn-danger me-1 mb-1\"  itle=\"Eliminar contacto\"  data-id=\"' + row.id + '\" data-action=\"delete-contacto\"><i class=\"far fa-trash-alt fa-sm\"></i></a>';
                    return ret;
                },
                "title": "Opciones"
            }
        ],
        "order": [
            [0, 'desc']
        ],
        "responsive": true,
        "paging": true,
        "searching": true,
        "info": true,
        "lengthMenu": [10, 25, 50, 100],
    });
</script>