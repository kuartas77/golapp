import * as yup from 'yup';
import dayjs from '@/utils/dayjs';

export const FILE_FIELDS = ['photo', 'player_document', 'medical_certificate', 'tutor_document', 'payment_receipt'];
export const SIGNATURE_FIELDS = ['signatureTutor', 'signatureAlumno'];
export const DATA_PROCESSING_POLICY_FIELD = 'data_processing_policy_accepted';

export const LEGACY_CONTRACTS = {
    inscription: {
        code: 'inscription',
        label: 'Contrato de inscripción',
        url: '',
        acceptance_field: 'contrato_insc',
        requires_acceptance: true,
        requires_tutor_signature: true,
        requires_player_signature: false,
    },
    affiliate: {
        code: 'affiliate',
        label: 'Contrato de afiliación y corresponsabilidad deportiva',
        url: '',
        acceptance_field: 'contrato_aff',
        requires_acceptance: true,
        requires_tutor_signature: true,
        requires_player_signature: true,
    },
};

const PHOTO_FILE_EXTENSIONS = ['jpg', 'jpeg', 'png'];
const PHOTO_FILE_MIME_TYPES = ['image/png', 'image/x-png', 'image/jpeg', 'image/pjpeg'];
const DOCUMENT_FILE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'pdf'];
const DOCUMENT_FILE_MIME_TYPES = [
    'image/png',
    'image/x-png',
    'image/jpeg',
    'image/pjpeg',
    'application/pdf',
    'application/acrobat',
    'application/nappdf',
    'application/x-pdf',
    'image/pdf',
];
const GENERIC_FILE_MIME_TYPES = ['', 'application/octet-stream'];

export const PHOTO_FILE_ACCEPT = [
    ...PHOTO_FILE_EXTENSIONS.map((extension) => `.${extension}`),
    ...PHOTO_FILE_MIME_TYPES,
].join(',');

export const DOCUMENT_FILE_ACCEPT = [
    ...DOCUMENT_FILE_EXTENSIONS.map((extension) => `.${extension}`),
    ...DOCUMENT_FILE_MIME_TYPES,
].join(',');

export const createBirthDateRange = () => {
    const today = dayjs();
    const minValue = today.subtract(20, 'year').startOf('year').toDate();
    const maxValue = today.subtract(3, 'year').endOf('year').toDate();

    return {
        minValue,
        maxValue,
        min: dayjs(minValue).format('YYYY-MM-DD'),
        max: dayjs(maxValue).format('YYYY-MM-DD'),
    };
};

const parseDate = (value) => {
    const [year, month, day] = String(value ?? '').split('-').map(Number);

    if (!year || !month || !day) {
        return null;
    }

    return new Date(year, month - 1, day);
};

const fileExtension = (file) => {
    const fileName = String(file?.name ?? '');
    const extensionSeparator = fileName.lastIndexOf('.');

    return extensionSeparator >= 0
        ? fileName.slice(extensionSeparator + 1).toLowerCase()
        : '';
};

const isAllowedDocumentFile = (file) => {
    if (!DOCUMENT_FILE_EXTENSIONS.includes(fileExtension(file))) {
        return false;
    }

    return GENERIC_FILE_MIME_TYPES.includes(file.type)
        || DOCUMENT_FILE_MIME_TYPES.includes(file.type);
};

const isAllowedPhotoFile = (file) => {
    if (!PHOTO_FILE_EXTENSIONS.includes(fileExtension(file))) {
        return false;
    }

    return GENERIC_FILE_MIME_TYPES.includes(file.type)
        || PHOTO_FILE_MIME_TYPES.includes(file.type);
};

export const createInscriptionSchema = ({
    fileSizeMb,
    school,
    acceptanceContracts,
    requiresTutorSignature,
    requiresPlayerSignature,
    minBirthDateValue,
    maxBirthDateValue,
}) => {
    const fileFieldSchema = (label, required = false, requiredMessage = `${label} es obligatorio.`) => {
        let schema = yup
            .mixed()
            .nullable()
            .test('file-type', `${label} debe estar en formato JPG, JPEG, PNG o PDF.`, (value) => {
                if (!value) {
                    return true;
                }

                return isAllowedDocumentFile(value);
            })
            .test('file-size', `${label} no puede superar ${fileSizeMb} MB.`, (value) => {
                if (!value) {
                    return true;
                }

                return value.size <= fileSizeMb * 1024 * 1024;
            });

        if (required) {
            schema = schema.required(requiredMessage);
        }

        return schema;
    };

    const photoFieldSchema = yup
        .mixed()
        .nullable()
        .test('file-type', 'La foto debe estar en formato JPG, JPEG o PNG.', (value) => {
            if (!value) {
                return true;
            }

            return isAllowedPhotoFile(value);
        })
        .test('file-size', `La foto no puede superar ${fileSizeMb} MB.`, (value) => {
            if (!value) {
                return true;
            }

            return value.size <= fileSizeMb * 1024 * 1024;
        });

    const acceptanceRules = Object.fromEntries(
        acceptanceContracts.map((contract) => [
            contract.acceptance_field,
            school.create_contract
                ? yup.boolean().oneOf([true], `Debes aceptar ${contract.label.toLowerCase()}.`)
                : yup.boolean(),
        ])
    );

    return yup.object({
        year: yup.string().required(),
        photo: photoFieldSchema,

        names: yup.string().trim().required('Ingresa los nombres.').max(50, 'Los nombres no pueden superar 50 caracteres.'),
        last_names: yup.string().trim().required('Ingresa los apellidos.').max(50, 'Los apellidos no pueden superar 50 caracteres.'),
        date_birth: yup
            .string()
            .required('Ingresa la fecha de nacimiento.')
            .test('date-format', 'Ingresa una fecha válida.', (value) => Boolean(parseDate(value)))
            .test('date-range', 'La fecha de nacimiento debe estar entre 3 y 20 años.', (value) => {
                const date = parseDate(value);

                if (!date) {
                    return false;
                }

                return date >= minBirthDateValue && date <= maxBirthDateValue;
            }),
        place_birth: yup.string().trim().required('Ingresa el lugar de nacimiento.').max(100, 'El lugar de nacimiento no puede superar 100 caracteres.'),
        identification_document: yup
            .string()
            .trim()
            .required('Ingresa el documento de identidad.')
            .max(50, 'El documento no puede superar 50 caracteres.')
            .matches(/^\d+$/, 'El documento solo debe contener números.'),
        document_type: yup.string().trim().required('Selecciona el tipo de documento.').max(50),
        gender: yup.string().trim().required('Selecciona el género.').max(50),
        email: yup.string().trim().required('Ingresa el correo electrónico.').email('Ingresa un correo válido.'),
        mobile: yup.string().trim().required('Ingresa un número telefónico.').max(50, 'El teléfono no puede superar 50 caracteres.'),
        medical_history: yup.string().nullable().max(200, 'Los antecedentes médicos no pueden superar 200 caracteres.'),

        address: yup.string().trim().required('Ingresa la dirección de residencia.').max(50, 'La dirección no puede superar 50 caracteres.'),
        municipality: yup.string().trim().required('Ingresa el municipio de residencia.').max(50, 'El municipio no puede superar 50 caracteres.'),
        neighborhood: yup.string().trim().required('Ingresa el barrio de residencia.').max(50, 'El barrio no puede superar 50 caracteres.'),
        rh: yup.string().trim().required('Selecciona el grupo sanguíneo.').max(50),
        eps: yup.string().trim().required('Ingresa la EPS.').max(50, 'La EPS no puede superar 50 caracteres.'),
        student_insurance: yup.string().nullable().max(50, 'El seguro estudiantil no puede superar 50 caracteres.'),
        school: yup.string().trim().required('Ingresa la institución educativa.').max(50, 'La institución educativa no puede superar 50 caracteres.'),
        degree: yup.string().trim().required('Selecciona el grado.').max(50),
        jornada: yup.string().trim().required('Selecciona la jornada.').max(50),

        tutor_name: yup.string().trim().required('Ingresa los nombres del acudiente.').max(50),
        tutor_num_doc: yup.string().trim().required('Ingresa el numero de documento del acudiente.').max(50),
        tutor_doc_exp: yup.string().trim().required('Ingresa el lugar de expedición del documento del acudiente.').max(100),
        tutor_relationship: yup.string().trim().required('Selecciona el parentesco del acudiente.').max(50),
        tutor_phone: yup.string().trim().required('Ingresa el teléfono del acudiente.').max(50),
        tutor_work: yup.string().trim().required('Ingresa la empresa del acudiente.').max(50),
        tutor_position_held: yup.string().trim().required('Ingresa el cargo del acudiente.').max(50),
        tutor_email: yup.string().trim().required('Ingresa el correo del acudiente.').email('Ingresa un correo válido.').max(50),

        signatureTutor: school.create_contract && requiresTutorSignature
            ? yup.string().required('Ingresa la firma del acudiente para continuar.')
            : yup.string().nullable(),
        signatureAlumno: school.create_contract && requiresPlayerSignature
            ? yup.string().required('Ingresa la firma del deportista para continuar.')
            : yup.string().nullable(),
        data_processing_policy_accepted: yup.boolean().oneOf(
            [true],
            'Debes autorizar el tratamiento de datos personales para continuar.'
        ),
        ...acceptanceRules,

        player_document: school.send_documents
            ? fileFieldSchema('El documento de identidad del deportista', true)
            : fileFieldSchema('El documento de identidad del deportista'),
        medical_certificate: school.send_documents
            ? fileFieldSchema('El certificado EPS', true)
            : fileFieldSchema('El certificado EPS'),
        tutor_document: school.send_documents
            ? fileFieldSchema(
                'El documento de identidad del acudiente escaneado',
                true,
                'Adjunta el documento de identidad escaneado del acudiente.'
            )
            : fileFieldSchema('El documento del acudiente'),
        payment_receipt: fileFieldSchema('El recibo de pago'),
    });
};

export const createDefaultInscriptionValues = ({ year, checkboxFields }) => ({
    year: String(year),
    photo: null,

    names: '',
    last_names: '',
    date_birth: '',
    place_birth: '',
    identification_document: '',
    document_type: '',
    gender: '',
    email: '',
    mobile: '',
    medical_history: '',

    address: '',
    municipality: '',
    neighborhood: '',
    rh: '',
    eps: '',
    student_insurance: 'Sura',
    school: '',
    degree: '',
    jornada: '',

    tutor_name: '',
    tutor_num_doc: '',
    tutor_doc_exp: '',
    tutor_relationship: '',
    tutor_phone: '',
    tutor_work: '',
    tutor_position_held: '',
    tutor_email: '',

    signatureTutor: '',
    signatureAlumno: '',
    ...Object.fromEntries(checkboxFields.map((field) => [field, false])),

    player_document: null,
    medical_certificate: null,
    tutor_document: null,
    payment_receipt: null,
});
