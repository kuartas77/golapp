import * as yup from 'yup';

yup.setLocale({
  mixed: {
    required: 'Este campo es obligatorio',
    default: 'No es válido',
  },
  string: {
    email: 'Debe ser un correo electrónico válido',
    min: 'Debe tener al menos ${min} caracteres',
    max: 'Debe tener como máximo ${max} caracteres',
    url: 'Debe ser una URL válida',
    trim: 'No debe contener espacios al principio o al final',
    lowercase: 'Debe estar en minúsculas',
    uppercase: 'Debe estar en mayúsculas',
  },
  number: {
    min: 'Debe ser mayor o igual a ${min}',
    max: 'Debe ser menor o igual a ${max}',
    positive: 'Debe ser un número positivo',
    negative: 'Debe ser un número negativo',
    integer: 'Debe ser un número entero',
  },
  date: {
    min: 'Debe ser posterior a ${min}',
    max: 'Debe ser anterior a ${max}',
  },
  boolean: {
    isValue: 'Debe ser ${value}',
  },
  object: {
    noUnknown: 'Tiene campos no permitidos: ${unknown}',
  },
  array: {
    min: 'Debe tener al menos ${min} elementos',
    max: 'Debe tener como máximo ${max} elementos',
  },
});