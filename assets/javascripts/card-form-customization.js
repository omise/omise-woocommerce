function setInitialValues() {
  Object.keys(DEFAULT_FORM_DESIGN).forEach(function (componentKey) {
    componentValues = DEFAULT_FORM_DESIGN[componentKey];
    Object.keys(componentValues).forEach(function (key) {
      setInputValue(`${componentKey}\\[${key}\\]`, componentValues[key])
    })
  });
}

function setInputValue(name, val) {
  document.querySelector(`[name=${name}]`).value = val
}

function getInputValue(name) {
  return document.querySelector(`[name=${name}]`).value
}

function setColorInputValue(element) {
  valueElement = element.nextElementSibling
  if (!valueElement) {
    element.insertAdjacentHTML('afterend', `<span>${element.value}</span>`);
  } else {
    valueElement.innerHTML = element.value;
  }
}

function handleColorInputChanges() {
  var colorInputs = document.querySelectorAll('.color-input-container');

  colorInputs.forEach((element) => {
    const input = element.querySelector('.color-input')

    setColorInputValue(input)
    input.addEventListener('change', (event) => {
      setColorInputValue(event.target)
    });

    element.addEventListener('click', (event) => {
      let input = event.target.querySelector('.color-input')
      if (!input) {
        input = event.target.previousElementSibling
      }
      var clickEvent = new MouseEvent('click');
      input.dispatchEvent(clickEvent);
    });
  })
}

function getDesignFormValues() {
  let formValues = DEFAULT_FORM_DESIGN
  Object.keys(DEFAULT_FORM_DESIGN).forEach(function (componentKey) {
    componentValues = DEFAULT_FORM_DESIGN[componentKey];
    Object.keys(componentValues).forEach(function (key) {
      const val = getInputValue(`${componentKey}\\[${key}\\]`)
      formValues[componentKey][key] = val;
    })
  });
  return formValues;
}

function initOmiseCardForm() {
  const customCardFormTheme = CARD_FORM_THEME ?? 'light';
  document.querySelector('.omise-modal .content').style.background =
    customCardFormTheme == 'light' ? 'white' : '#272934'
  showOmiseEmbeddedCardForm({
    element: document.getElementById('omise-card'),
    publicKey: PUBLIC_KEY, 
    locale: LOCALE,
    theme: customCardFormTheme, 
    design: getDesignFormValues()
  })
}

document.getElementById('form-preview').addEventListener('click', (event) => {
  event.preventDefault()
  initOmiseCardForm()
  document.querySelector('.omise-modal').style.display = 'flex'
});

document.getElementById('close-form-preview').addEventListener('click', (event) => {
  event.preventDefault()
  document.querySelector('.omise-modal').style.display = 'none'
});

document.getElementById('omise-modal').addEventListener('click', (event) => {
  if (event.target.id == 'omise-modal') {
    document.querySelector('.omise-modal').style.display = 'none'
  }
});

setInitialValues();
handleColorInputChanges();
