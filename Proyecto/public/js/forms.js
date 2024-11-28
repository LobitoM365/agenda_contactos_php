async function getDataModal(data) {

    console.log(data)

    let response = {};


    switch (data.action) {
        case "delete-imagen":

            response["title"] = "Eliminar imagen";
            response["action"] = "";
            response["button"] = "Delete";
            response["action"] = api + `contactos/eliminar/imagen/` + data.id;
            response["method"] = "DELETE";

            response["close-eval"] = `
                  $('#contactos').DataTable().ajax.reload();
                  $("#modalContent .close")[0].click()
            `;

            response["warning"] = {
                "tittle": '¿Esta seguro de eliminar el la imagen del contacto ' + data.id + '?',
                "id": data.id
            };
            break;
        case "cambiar-imagen":

            response["title"] = "Cambiar imagen";
            response["action"] = "";
            response["button"] = "Delete";
            response["preconfirm"] = false;
            response["action"] = api + `contactos/cambiar/imagen/` + data.id;
            response["method"] = "POST";

            response["close-eval"] = `
                    $('#contactos').DataTable().ajax.reload();
                    $("#modalContent .close")[0].click()
            `;

            response["warning"] = {
                "tittle": '¿Esta seguro de cambiar el la imagen del contacto ' + data.id + '?',
                "id": data.id
            };

            response["form_data"] = `
                function() {
                    let formData = new FormData();
                    formData.append("imagen", $("#imagen")[0].files[0]);

                    console.log($("#imagen")[0].files[0])
                    return formData;
                }
            `;

            break;
        case "delete-contacto":

            response["title"] = "Eliminar contacto";
            response["action"] = "";
            response["button"] = "Delete";
            response["action"] = api + `contactos/eliminar/` + data.id;
            response["method"] = "DELETE";

            response["close-eval"] = `
            $('#contactos').DataTable().ajax.reload();
            `;

            response["warning"] = {
                "tittle": '¿Esta seguro de eliminar el contacto ' + data.id + '?',
                "id": data.id
            };
            break;
        case "edit-contacto":

            const contacto = await getFetch(api + "contactos/buscar/" + data.id, "GET", []);

            console.log(contacto, "aaaaaaaaaaaa")

            response["html"] = `
            <div class="content-form-create-contact">
            `;
            if (contacto.data[0].imagen) {
                response["html"] += `
                <div class="content-load-img">
                    <h3> Subir imagen </h3>
                    <div id="imagen_contacto" class="div-content-img-load size-huella">
                        <div class="div-quit-img">
                            <div class="div-icon-quit-load-file">
                                <i class="icon-quit-img-load fa-solid fa-xmark" style="color: #ff0000;"></i>
                            </div> 
                            <div class="div-view-img-load">
                                <img class="img-vertical-true" src="${contacto.data[0].imagen}">
                            </div> 
                        </div>
                    </div>
                    <input accept="image/*" id="imagen" name="imagen" type="file" class="input-file-load input-file-hidden">
                    <div class="content-buttons-img-contacto">
                      <button type="button" data-id="${contacto.data[0].id}" data-action="cambiar-imagen" class="button-delete btn btn-success">Guardar</button>
                      <button type="button" data-id="${contacto.data[0].id}" data-action="delete-imagen" class="button-delete btn btn-danger">Quitar</button>
                    </div>
                </div> 
                `
            } else {
                response["html"] += `
                <div class="content-load-img">
                    <h3> Subir imagen </h3>
                    <div id="imagen_contacto" class="div-content-img-load size-huella" >
                        <div class="div-load-img">
                            <img src='public/img/iconLoadImg.png' class="img-size" />
                        </div>
                    </div>
                    <div class="content-buttons-img-contacto">
                      <button type="button" data-id="${contacto.data[0].id}" data-action="cambiar-imagen" class="button-delete btn btn-success">Guardar</button>
                    </div>
                    <input accept="image/*" id="imagen" name="imagen" type="file" class="input-file-load input-file-hidden" />
                </div>  
                `
            }
            response["html"] += `

                

                <div class="div-content-form-create-contact">
                    <div class="mb-3">
                        <label for="nombre" class="form-label col-form-label ">Nombre:</label>
                        <div class="">
                            <input value="${contacto.data[0].nombre}" class="form-control" name="nombre" id="nombre"/>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="telefono" class="form-label col-form-label ">Teléfono:</label>
                        <div class="">
                            <input value="${contacto.data[0].telefono}" class="form-control" name="telefono" id="telefono"/>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edad" class="form-label col-form-label ">Edad:</label>
                        <div class="">
                            <input value="${contacto.data[0].edad}" class="form-control" name="edad" id="edad"/>
                        </div>
                    </div>

                    <div class="content-texta-area-create-contact mb-3">
                        <label for="descripcion" class="form-label col-form-label ">Descripcion:</label>
                        <div class="">
                            <textarea value="${contacto.data[0].desccripcion}" class="form-control" name="descripcion" id="descripcion"> </textarea>
                        </div>
                    </div>
                </div>

            </div>`;

            response["title"] = "Editar contacto";
            response["modal_class"] = "modal-create-contact";
            response["script"] = ``;
            response["route"] = `contactos/editar/` + data.id;
            response["table"] = `#contactos`;
            response["method"] = "POST";
            break;
        case "create-contact":

            response["html"] = `
            <div class="content-form-create-contact">
                <div class="content-load-img">
                    <h3> Subir imagen </h3>
                    <div id="imagen_contacto" class="div-content-img-load size-huella" >
                        <div class="div-load-img">
                            <img src='public/img/iconLoadImg.png' class="img-size" />
                        </div>
                    </div>
                    <input accept="image/*" id="imagen" name="imagen" type="file" class="input-file-load input-file-hidden" />
                </div>  

                <div class="div-content-form-create-contact">
                    <div class="mb-3">
                        <label for="nombre" class="form-label col-form-label ">Nombre:</label>
                        <div class="">
                            <input class="form-control" name="nombre" id="nombre"/>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="telefono" class="form-label col-form-label ">Teléfono:</label>
                        <div class="">
                            <input class="form-control" name="telefono" id="telefono"/>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edad" class="form-label col-form-label ">Edad:</label>
                        <div class="">
                            <input class="form-control" name="edad" id="edad"/>
                        </div>
                    </div>

                    <div class="content-texta-area-create-contact mb-3">
                        <label for="descripcion" class="form-label col-form-label ">Descripcion:</label>
                        <div class="">
                            <textarea class="form-control" name="descripcion" id="descripcion"> </textarea>
                        </div>
                    </div>
                </div>

            </div>`;

            response["title"] = "Crear contacto";
            response["modal_class"] = "modal-create-contact";
            response["script"] = ``;
            response["route"] = `contactos/crear`;
            response["table"] = `#contactos`;

        default:
            response["error"] = `Error en la solicitud`;
            break;
    }

    return response
}