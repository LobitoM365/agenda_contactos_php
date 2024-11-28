var modalContentClass;
var submitForm;
var confirmAction;
var routeForm;
var methodForm;
var table;

$(document).on("show.bs.modal", ".modal-class", async function (e) {

    if (modalContentClass) {
        $(".modal-class").removeClass(modalContentClass)
    }


    var $element = $(e.relatedTarget);
    var json = {};

    $element.each(function () {
        $.each(this.attributes, function (index, attribute) {
            if (attribute.name.startsWith('data-')) {
                var key = attribute.name.slice(5);
                json[key] = $(e.relatedTarget).attr(attribute.name);
            }
        });
    });



    const response = await getDataModal(json)

    console.log(json, "ressssss")


    if (response.title) {
        $("#modalTitle").html(response.title)
    } else {
        $("#modalTitle").html("")
    }
    if (response.route) {
        routeForm = response.route
    }
    if (response.method) {
        methodForm = response.method
    } else {
        methodForm = null;
    }
    if (response.table) {
        table = response.table
    }
    if (response.confirm) {
        confirmAction = response.confirm
    }
    if (response.confirm_message) {
        confirmActionMessage = response.confirm_message
    }
    if (response.confirm_title) {
        confirmActionMessageTitle = response.confirm_title
    }
    if (response.submit == false) {
        submitForm = false
    } else {
        submitForm = true
    }
    if (response.script) {
        response.script = response.script.replace(/\$\("body"\)\.on/g, '$(".modal-class").on').replace(/\$\(document\)\.on/g, '$(".modal-class").on').replace(/\$\("document"\)\.on/g, '$(".modal-class").on')
        eval(response.script)
    }
    if (response.modal_class) {
        $(".modal-class").addClass(response.modal_class)
        modalContentClass = response.modal_class
    }

    if (response.error) {
        $("#modalError").html("<div class='content-modal-error'>" + response.error + "</div>")
    } else {
        $("#modalError").html("")
    }

    if (response.html) {
        $("#modalBody").html(response.html)
    } else {
        $("#modalBody").html("")
    }

})

$("body").on("hidden.bs.modal", ".modal-class", function () {
    $("#modalBody").html("");
    $("#modalFooter").html("");
    $(".modal-class").off();
})


async function getFetch(route, method, data) {
    try {
        return new Promise((resolve, reject) => {
            // Código asíncrono aquí
            fetch(route, {
                method: method,
            })
                .then(response => response.json())
                .then(data => {
                    resolve(data)
                })
                .catch(error => {
                    console.log(error)
                    resolve(error)
                })
        });
    } catch (error) {
        resolve(error)
    }
}





$("body").on('dragenter', function (e) {
    e.preventDefault();
    const divImgLoad = $("#divLoadImgConejita");
    if (divImgLoad.length > 0) {
        if ($('#modalForm').hasClass('show')) {
            if ($("#divImgDrag").length == 0) {
                $("body").append(templateBodyDrag)
                $("body").css("cursor", "pointer")
            }
        }
    }
});

$("body").on('dragover', function (e) {
    e.preventDefault();
    const divImgLoad = $("#divLoadImgConejita");
    if (divImgLoad) {

    }
});

$("body").on('dragleave', function (e) {
    e.preventDefault();
});


$("body").on('dragend', function (e) {
    e.preventDefault();
    const divImgLoad = $("#divLoadImgConejita");
    if (!$("#divImgDrag").length == 0) {
        $("#divImgDrag").remove()
    }
})
$("body").on('drop', function (e) {
    e.preventDefault();
    const divImgLoad = $("#divLoadImgConejita");
    if (!$("#divImgDrag").length == 0) {
        $("#divImgDrag").remove()
    }

    if ($('#modalForm').hasClass('show')) {
        if (divImgLoad) {
            var file = e.originalEvent.dataTransfer.files[0];
            var formData = new FormData();
            formData.append('imagen', file);
            formData.append('conejitas_id', conejitaFocus);
            sendImg(formData)
            console.log('Nombre del archivo:', file.name);
            console.log('Tipo del archivo:', file.type);
            console.log('Tamaño del archivo:', file.size, 'bytes');
        }
    }
});

function getElementDrag(type) {
    if (type == 1) {

    }
}

$(window).on('blur', function () {
    if (!$("#divImgDrag").length == 0) {
        $("#divImgDrag").remove()
    }
});
$(window).on('click', function () {
    if (!$("#divImgDrag").length == 0) {
        $("#divImgDrag").remove()
    }
});




$("body").on("click", ".div-load-img", function () {
    let parent = $(this).closest(".div-content-img-load");
    if ($(parent).find($("input[type=\'file\']")).length == 0) {
        parent = $(parent).parent();
    }

    /* $("input[name=\'" + $(this).parent().attr("id") + "\']").trigger("click"); */
    $(parent).find($("input[type=\'file\']")).trigger("click");
})


$("body").on("dragover", ".div-load-img", function (e) {

    e.preventDefault();
    if ($(this).find(".div-drag-load-img").length == 0) {
        $(this).append("<div class=\'div-drag-load-img\'> <img src=\'/public/img/iconDragImg.png\' /> </div>")
    }
    var rect = this.getBoundingClientRect();
    var x = event.clientX;
    var y = event.clientY;

    if (!(x > rect.left && x < rect.right && y > rect.top && y < rect.bottom)) {
        $(this).find(".div-drag-load-img").remove();
    }
})



$("body").on("click", ".icon-quit-img-load", function () {
    let parent = $(this).closest(".div-content-img-load");
    if ($(parent).find($("input[type=\'file\']")).length == 0) {
        parent = $(parent).parent();
    }



    /* $("input[name=\'" + $(this).closest(".div-content-img-load").attr("id") + "\']").val("") */
    $(parent).find($("input[type=\'file\']")).val("")
    let srcImg = "public/img/iconLoadImg.png";

    if ($(this).closest(".div-content-img-load").attr("data-src-img")) {
        srcImg = $(this).closest(".div-content-img-load").attr("data-src-img");
    }
    $(this).closest(".div-content-img-load").html("<div class=\'div-load-img\'> <img src=\'" + srcImg + "\'  /></div>");

})

$("body").on("change", ".input-file-load", function () {
    var fileInput = this;
    var parentInsert = $(this).siblings(".div-content-img-load");

    loadImgDrag(fileInput, parentInsert)

});

$("body").on("drop", ".div-load-img", async function (event) {
    event.preventDefault(); // Prevenir comportamiento por defecto (abrir el archivo en el navegador)
    $(this).find(".div-drag-load-img").remove();
    const inputFile = $("input[name=\'" + $(this).parent().attr("id") + "\']");
    var file = event.originalEvent.dataTransfer.files;

    loadImgDrag(file, $(this).parent())
    inputFile.prop("files", event.originalEvent.dataTransfer.files);

    if (inputFile.closest(".div-parent-evidencia").length > 0) {
        const imgData = await obtenerMacSn(event.originalEvent.dataTransfer.files[0])
        obtenerSNyMAC(imgData, inputFile)
    }
});

function loadImgDrag(fileInput, parentInsert) {
    let fileLoad = fileInput.files;
    if (fileInput.files) {
        fileLoad = fileInput.files[0]
    } else {
        fileLoad = fileInput[0]
    }



    if (fileLoad) {
        var reader = new FileReader();
        reader.onload = async function (e) {
            var imgSrc = e.target.result;


            const img_vertical = await new Promise((resolve, reject) => {
                var img = new Image();
                img.src = imgSrc;

                img.onload = function () {
                    if (img.height > (img.width + 40)) {
                        resolve(true);
                    } else {
                        resolve(false);
                    }
                };

                img.onerror = function () {
                    resolve(false);
                };
            });

            if (fileLoad.type.match("image.*")) {
                $(parentInsert).html("<div class=\'div-quit-img\'><div class=\'div-icon-quit-load-file\' ><i class=\'icon-quit-img-load fa-solid fa-xmark\' style=\'color: #ff0000;\'></i></div> <div class=\"div-view-img-load\"><img  class=\"img-vertical-" + img_vertical + "\" src=\'" + imgSrc + "\' /></div> </div>")
            } else {
                console.log("El archivo seleccionado no es una imagen.");
            }
        }
        reader.readAsDataURL(fileLoad);
    };

}



$("body").on("submit", "#formModal", function (e) {
    e.preventDefault()
    if (submitForm) {
        const formData = new FormData(e.target)
        if (confirmAction == true) {
            Swal.fire({
                title: confirmActionMessageTitle,
                text: confirmActionMessage,
                icon: "warning",
                buttons: {
                    "cancelar": {
                        "value": "cancelar",
                        "className": "button-cancel-alert"
                    },
                    "confirmar": {
                        "value": "confirmar",
                        "text": "Confirmar",
                    }
                }
            }).then((value) => {
                if (value === "confirmar") {
                    sendInputsForm(formData, routeForm, methodForm)
                }
            })
            return
        }
        sendInputsForm(formData, routeForm, methodForm)
    }
})

function sendInputsForm(formData, routeForm, methodForm) {


    // Definir los headers
    let addHeaders = {};

    // Verificar si el 'formData' es de tipo FormData o no
    if (!(formData instanceof FormData)) {
        // Si no es FormData, se asume que estamos enviando un JSON
        addHeaders = {
            "Content-Type": "application/json"
        };
        formData = JSON.stringify(formData);  // Convertir el objeto a JSON
    }

    // Realizar el fetch
    fetch(api + routeForm, {
        method: (methodForm ? methodForm : "POST"),
        headers: addHeaders,
        body: formData
    })
        .then(response => {
            // Verificar si la respuesta del servidor es correcta
            if (!response.ok) {
                throw new Error(`Error en la respuesta del servidor: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            methodForm = null;


            // Comprobar si existen errores

            console.log(data.errors)
            if (data.errors) {
                setErrorsMessage(data.errors);
            } else if (data.status === true) {
                // Mostrar mensaje de éxito con SweetAlert
                Swal.fire({
                    title: "Excelente",
                    text: data.message,
                    icon: "success",
                });

                // Evaluar código JS (si se pasa un eval)
                if (data.eval) {
                    eval(data.eval);
                }


                $("#modalContent .close")[0].click()



                // Recargar la tabla (si existe)
                if (table) {
                    const tableReload = $(table).DataTable();
                    tableReload.ajax.reload();
                }

            } else if (data.status === false) {
                Swal.fire({
                    title: "Inténtalo de nuevo",
                    text: data.message,
                    icon: "error",
                });
            } else {
                Swal.fire({
                    title: "Inténtalo de nuevo",
                    text: data.message,
                    icon: "error",
                });
            }
        })
        .catch(error => {
            console.error(error);

            Swal.fire({
                title: "Inténtalo de nuevo",
                text: `Error: ${error.message}`,
                icon: "error",
            });
        });

}



function setErrorsMessage(data) {
    let h6ErrorsForms = document.querySelectorAll(".h6-errors-form");
    for (let x = 0; x < h6ErrorsForms.length; x++) {
        if (h6ErrorsForms[x]) {
            h6ErrorsForms[x].remove()
        }
    }
    if (typeof data == "object") {
        const keys = Object.keys(data);
        for (let x = 0; x < keys.length; x++) {
            const element = document.getElementById(keys[x])
            if (element) {
                let parentInput = element.closest(".parent-input");

                let h6 = document.createElement("h6")
                h6.innerHTML = data[keys[x]];
                h6.classList.add("h6-errors-form")
                if (parentInput) {

                } else {
                    const parent = element.parentNode
                    if (parent) {
                        parentInput = parent
                    }
                }
                parentInput.appendChild(h6)
            }
        }
    }
}


$(document).on("click", ".button-delete", async function (e) {
    const id = $(this).data("id");
    console.log($(this).data("action"))
    let jsonData = {
        "action": $(this).data("action"),
        "id": $(this).data("id")
    };

    let dataButton = {};
    Array.from(this.attributes).forEach(attr => {
        if (attr.name.startsWith("data-")) {
            dataButton[attr.name.slice(5)] = attr.value;
        }
    });

    jsonData = { ...jsonData, ...dataButton }

    const data = await getDataModal(jsonData);


    if (data["close-eval"]) {
        closeEval = data["close-eval"]
    }


    console.log(data)
    if (data.warning) {
        let dataObject = {};

        Swal.fire({
            title: "Cuidado",
            html: data.warning.tittle,
            icon: "warning",
            showCancelButton: true,     // Muestra el botón de cancelar
            confirmButtonText: "Aceptar", // Texto del botón de confirmación
            confirmButtonColor: "#3085d6", // Color del botón de confirmación
            cancelButtonText: "Cancelar", // Texto del botón de cancelar
            cancelButtonColor: "#d33",
            showCloseButton: true,
            allowOutsideClick: false,
            allowOutsideClick: () => !Swal.isLoading(),
            preConfirm: async () => {
                const div = $("#swal2-html-container");
                $.each(div.data(), (key, value) => {
                    dataObject[key] = value;
                });

                // Obtener todos los inputs dentro del div
                div.find("input, select, textarea").each(function () {
                    dataObject[$(this).attr("name")] = $(this).val();
                });


                var dataEval = {};

                if (data["data-eval"]) {
                    try {
                        dataEval = eval("(" + data["data-eval"] + ")()");
                    } catch (error) {
                        console.log(error)
                    }
                }




                if (typeof dataEval != "object") {
                    dataEval = {};
                }

                jsonData = { ...jsonData, ...dataButton }

                dataObject = { ...dataObject, ...data.warning, ...dataEval, ...dataButton }


                let formData = new FormData();

                for (let key in dataObject) {
                    if (dataObject.hasOwnProperty(key)) {
                        // Si el valor es un archivo, agregamos el archivo
                        if (dataObject[key] instanceof File) {
                            formData.append(key, dataObject[key]);
                        } else if (dataObject[key] !== undefined && dataObject[key] !== null) {
                            // Si el valor no es un archivo, agregamos el valor normal
                            formData.append(key, dataObject[key]);
                        }
                    }
                }

                let form_data_eval;

                if (data["form_data"]) {
                    try {
                        form_data_eval = eval("(" + data["form_data"] + ")()");
                        formData = mergeFormData(formData, form_data_eval)
                    } catch (error) {
                        console.log(error)
                    }

                }

                return new Promise((resolve, reject) => {
                    $.ajax({
                        type: (data.method ? data.method : "POST"),
                        url: data.action,
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            console.log(response, "ressssssssssss")

                            console.log(data, "--------------")

                            if (data.preconfirm === false) {
                                return resolve(response);
                            }

                            if (response.errors) {
                                setDataErrors(response, true, false);
                                resolve(Swal.showValidationMessage(response.message))
                            } else {
                                resolve(response);
                            }
                        },
                        error: function (error) {
                            reject(Swal.showValidationMessage("Error en la solicitud."));
                        }
                    });
                });


            }

        }).then((value) => {
            if (value.isConfirmed) {
                try {
                    if (value.value["alert-eval"]) {
                        eval(value.value["alert-eval"])

                    }
                } catch (error) {
                    console.log(error)
                }
                try {
                    if (value.value["close-eval"]) {
                        closeEval = value.value["close-eval"]
                    }

                } catch (error) {
                    console.log(error)
                }

                console.log(value.value, value.value["alert-close-true-eval"])
                try {

                    if (value.value["alert-close-true-eval"]) {
                        eval(value.value["alert-close-true-eval"])
                    }
                } catch (error) {
                    console.log(error)
                }

                try {
                    if (value.value["eval"]) {
                        eval(value.value["eval"])
                    }
                } catch (error) {
                    console.log(error)
                }
                try {
                    if (closeEval) {
                        value.value["close-eval"] = closeEval;
                    }
                } catch (error) {
                    console.log(error)
                }



                return setDataErrors(value.value, true, true);
            }
        });
        if (data["eval"]) {
            try {
                eval(data["eval"]);
            } catch (error) {

            }
        }
    } else if (!data.delete_error) {
        console.log(data.message, "data")
        Swal.fire({
            title: "Inténtalo de nuevo",
            html: data.message ? data.message : "Error en la solicitud.",
            icon: "error",
            buttons: {
                cancel: {
                    text: "OK",
                    value: null,
                    visible: true,
                    className: "btn btn-default",
                    closeModal: true,
                }
            }
        })
    } else if (data.status == false) {
        Swal.fire({
            title: "Inténtalo de nuevo",
            html: data.message ? data.message : "Error en la solicitud.",
            icon: "error",
            buttons: {
                cancel: {
                    text: "OK",
                    value: null,
                    visible: true,
                    className: "btn btn-default",
                    closeModal: true,
                }
            }
        })
    }
    console.log(data)

});


function setDataErrors(data, statusErrors, statusAlerts, parentElement, evals) {

    let parentSearch = document;

    if (parentElement instanceof Element) {
        parentSearch = parentElement;

    } else if (typeof parentElement === 'string') {
        parentSearch = document.querySelector(parentElement);
    }






    const h6Error = parentSearch.querySelectorAll(".h6-error");
    if (h6Error) {
        for (let s = 0; s < h6Error.length; s++) {
            h6Error[s].remove();
        }
    }

    const borderError = parentSearch.querySelectorAll(".border-error");
    if (borderError) {
        for (let b = 0; b < borderError.length; b++) {

            borderError[b].style.border = "";
            borderError[b].classList.remove("border-error");

        }
    }
    if (statusErrors != false) {
        if (Object.keys(data.errors ? data.errors : {}).length > 0) {
            let keysErrors = Object.keys(data.errors);

            const trError = parentSearch.querySelectorAll(".tr-error");
            if (trError) {
                for (let t = 0; t < trError.length; t++) {
                    let hermano = trError[t].previousElementSibling;

                    if (hermano) {
                        hermano.style.border = "";
                    }
                    trError[t].remove();

                }
            }

            const functionOne = data.type == "line" ? setBorderError : seth6Error;
            const functionTwo = data.exeption_type == "line" ? setBorderError : data.exeption_type == "text" ? seth6Error : seth6Error;

            for (let x = 0; x < keysErrors.length; x++) {


                if (data.validation_for ? data.validation_for : "" == "table") {
                    seth6Error(keysErrors[x], "table")
                    if (parentSearch.querySelector("#" + keysErrors[x])) {
                        if (parentSearch.querySelector("#" + keysErrors[x]).closest("tr")) {
                            parentSearch.querySelector("#" + keysErrors[x]).closest("tr").style.borderTop = "1px solid red";
                            parentSearch.querySelector("#" + keysErrors[x]).closest("tr").style.borderLeft = "1px solid red";
                            parentSearch.querySelector("#" + keysErrors[x]).closest("tr").style.borderRight = "1px solid red";
                        }

                    }

                } else {
                    let exeptions = data.exeptions ? Object.keys(data.exeptions) : [];

                    let countExeption = 0;
                    for (let e = 0; e < exeptions.length; e++) {
                        for (let ke = 0; ke < data.exeptions[exeptions[e]].length; ke++) {
                            if (exeptions[e] == "line") {
                                if (data.exeptions[exeptions[e]][ke] == keysErrors[x]) {
                                    countExeption = countExeption + 1;
                                    setBorderError(keysErrors[x])
                                }
                            } else if (exeptions[e] == "doble") {
                                if (data.exeptions[exeptions[e]][ke] == keysErrors[x]) {
                                    countExeption = countExeption + 1;
                                    setBorderError(keysErrors[x])
                                    seth6Error(keysErrors[x])
                                }
                            } else if (exeptions[e] == "text") {

                                if (data.exeptions[exeptions[e]][ke] == keysErrors[x]) {
                                    countExeption = countExeption + 1;
                                    seth6Error(keysErrors[x])
                                }
                            }
                        }

                    }
                    if (countExeption == 0) {

                        if (data.type == "doble") {
                            seth6Error(keysErrors[x])
                            setBorderError(keysErrors[x])
                        } else {
                            functionOne(keysErrors[x])
                        }

                    } else {
                        countExeption = 0;
                    }
                }
            }

            function setBorderError(key) {

                if (parentSearch.querySelector("#" + key)) {

                    if (parentSearch.querySelector("#" + key).parentNode.querySelectorAll(".select2-container").length > 0) {

                        parentSearch.querySelector("#" + key).parentNode.querySelectorAll(".select2-selection ")[0].style.border = "1.5px solid red";
                        parentSearch.querySelector("#" + key).parentNode.querySelectorAll(".select2-selection ")[0].classList.add("border-error");
                    } else {
                        parentSearch.querySelector("#" + key).style.border = "1.5px solid red";
                        parentSearch.querySelector("#" + key).classList.add("border-error");
                    }

                }
            }

            function seth6Error(key, validationFor) {

                let h6Error = document.createElement("h6");
                h6Error.innerHTML = data.errors[key]
                h6Error.classList.add("text-red")
                h6Error.classList.add("h6-error")
                if (parentSearch.querySelector("#" + key)) {
                    if (validationFor == "table" && parentSearch.querySelector("#" + key).closest("tr")) {
                        let filaTr = parentSearch.querySelector("#" + key).closest("tr");
                        let indice = Array.from(filaTr.parentNode.children).indexOf(filaTr);
                        let tr = document.createElement("tr");
                        tr.style.borderBottom = "1px solid red";
                        tr.style.borderLeft = "1px solid red";
                        tr.style.borderRight = "1px solid red";
                        let td = document.createElement("td");
                        tr.classList.add("tr-error")
                        td.appendChild(h6Error);
                        td.setAttribute("colSpan", "1000");
                        tr.appendChild(td);
                        let table = filaTr.closest("table");
                        $(table).find("tr").eq(indice + 1).after(tr);
                    } else {
                        if (!parentSearch.querySelector("#" + key).parentNode.getElementsByTagName("h6")[0]) {
                            parentSearch.querySelector("#" + key).parentNode.appendChild(h6Error);
                        }
                    }

                }
            }
        }
    }
    if (!data.errors) {
        if (evals != false) {
            
            try {
                if (data["alert-eval"]) {
                    if (eval(data["alert-eval"])) {
                        eval(data["alert-eval"])
                    }
                }
            } catch (error) {
                console.log(error)
            }
            try {
                if (data["close-eval"]) {
                    closeEval = data["close-eval"]
                    eval(data["close-eval"])
                }

            } catch (error) {
                console.log(error)
            }
            try {
                if (data["eval"]) {
                    if (eval(data["eval"])) {
                        eval(data["eval"])
                    }
                    closeEval = data["eval"]
                }
            } catch (error) {
                console.log(error)
            }

        }
        if (statusAlerts != false) {
            if (data.status === false || data.server_error === true) {
                Swal.fire({
                    title: "Inténtalo de nuevo",
                    html: data.message ? data.message : "Error en la solicitud.",
                    icon: "error",
                    cancelButtonText: "Entiendo",
                    cancelButtonColor: "#d33",
                    showCloseButton: true,
                });
            } else if (data.status === true || data.server_error === false) {
                Swal.fire({
                    icon: "success",
                    title: "Excelente",
                    html: data.message ? data.message : "Se realizó la acción exitosamente.",
                    confirmButtonText: "Ok",
                    confirmButtonColor: "#3085d6",
                }).then((value) => {

                    if (value.isConfirmed) {
                        if (evals != false) {

                            try {

                                if (data["alert-close-true-eval"]) {
                                    if (eval(data["alert-close-true-eval"])) {
                                        eval(data["alert-close-true-eval"])
                                    }
                                }
                            } catch (error) {
                                console.log(error)
                            }
                        }
                    }
                    /* timer: 1500 */
                });
            }
        }

    }
}


function mergeFormData(formData1, formData2) {
    // Crear un nuevo FormData para almacenar la combinación
    let combinedFormData = new FormData();

    // Iterar sobre los datos del primer FormData (formData1)
    formData1.forEach((value, key) => {
        combinedFormData.append(key, value);
    });

    // Iterar sobre los datos del segundo FormData (formData2)
    formData2.forEach((value, key) => {
        combinedFormData.append(key, value);
    });

    return combinedFormData;
}