/**
 * Handles form submission via AJAX with field modification capability
 * @param {string} formSelector - Form selector
 * @param {object} options - Configuration options
 * @param {string} options.url - API endpoint
 * @param {string} options.operation - HTTP method (create, update, etc.)
 * @param {function} [options.modifyPayload] - Function to modify payload before send
 * @param {function} [options.beforeSend] - Callback before sending
 * @param {function} [options.success] - Callback on success
 * @param {function} [options.error] - Callback on error
 * @param {string} [options.redirect] - URL to redirect to after success
 * @param {string} [options.type] - Http method
 */
function setupFormAjax(formSelector, options) {
  $(document).ready(function () {
    $(formSelector).on("submit", async function (e) {
      e.preventDefault();
      const $form = $(this);
      let payload = {};

      $form.serializeArray().forEach((item) => {
        if (item.value) payload[item.name] = item.value;
      });

      $form.find(":disabled").each(function () {
        const name = $(this).attr("name");
        const value = $(this).val();
        if (name && !payload[name]) {
          payload[name] = value;
        }
      });

      if (options.files && options.files.length > 0) {
        try {
          for (const fileOption of options.files) {
            const fileInput = $(`#${fileOption.inputId}`);

            if (fileInput && fileInput.files && fileInput.files.length > 0) {
              const base64 = await getFileBase64FromInput(fileOption.inputId);
              if (!base64) return;

              payload[fileOption.key] = base64;
            }
          }
        } catch (error) {
          console.error(error);
          const errorMsg = "Error procesando archivos";
          if (options.error) options.error(errorMsg);
          else alertify.error(errorMsg);

          handleStopLoading(formSelector);
          return;
        }
      }

      if (options.modifyPayload) {
        payload = options.modifyPayload(payload, $form);
      }

      if (options.beforeSend) {
        options.beforeSend(payload);
      }

      $.ajax({
        type: "POST",
        url: options.url,
        data: {
          operation: options.operation,
          data: payload,
        },
      })
        .done(function (result) {
          handleSuccess(result, options);
        })
        .fail(function (xhr) {
          handleError(xhr, options, formSelector);
        });
    });
  });

  function handleSuccess(result, options) {
    if (!result.success) {
      const errorMsg = result.message || "Error en la operación";
      return handleError(errorMsg, options);
    }

    if (options.success) {
      options.success(result);
    }

    if (options.operation !== "login") {
      alertify.success("Operación exitosa");
    }

    handleStopLoading(formSelector);

    if (options.redirect) {
      setTimeout(() => {
        window.location.href = options.redirect;
      }, 1500);
    }
  }

  function handleError(error, options, formSelector = "") {
    const errorMsg =
      error.responseJSON?.message || error.message || "Error en la operación";

    if (options.error) {
      options.error(errorMsg);
    } else {
      alertify.error(errorMsg);
    }

    handleStopLoading(formSelector);
  }

  function handleStopLoading(formSelector) {
    const btnSelector = `#${formSelector.replace("#", "")}submitBtn`;
    const submitBtn = $(btnSelector).get(0);

    if (submitBtn) {
      stopButtonLoading(submitBtn);
    }
  }
}

function formSectionVisibility(show, sectionId, inputsIds = []) {
  const $section = $(`#${sectionId}`);

  if (show) {
    $section.show();
    inputsIds.forEach((input) => $(`#${input}`).prop("required", true));
  } else {
    inputsIds.forEach((input) => $(`#${input}`).removeAttr("required"));
    $section.hide();
  }
}
