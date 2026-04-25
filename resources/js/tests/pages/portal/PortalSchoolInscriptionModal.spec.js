import { flushPromises, mount } from '@vue/test-utils';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

const apiMock = {
    get: vi.fn(),
    post: vi.fn(),
    interceptors: {
        request: {
            use: vi.fn(),
        },
    },
};

const recaptchaLoadedMock = vi.fn();
const executeRecaptchaMock = vi.fn();

vi.mock('axios', () => ({
    default: {
        create: vi.fn(() => apiMock),
    },
}));

vi.mock('vue-recaptcha-v3', () => ({
    useReCaptcha: () => ({
        recaptchaLoaded: recaptchaLoadedMock,
        executeRecaptcha: executeRecaptchaMock,
    }),
}));

import PortalSchoolInscriptionModal from '@/pages/portal/PortalSchoolInscriptionModal.vue';

const wrappers = [];

const defaultSchool = {
    id: 10,
    name: 'Escuela Demo',
    create_contract: false,
    send_documents: false,
    sign_player: false,
};

const defaultProps = {
    school: defaultSchool,
    year: 2026,
    fileSizeMb: 3,
    storageKey: 'portal-inscription-modal-test',
    endpoints: {
        autocomplete: '/api/autocomplete',
        searchDoc: '/api/search-doc',
        store: '/api/store',
    },
    assets: {
        defaultUserPhoto: '/img/default-user.png',
    },
    contracts: {
        affiliate: '/contracts/affiliate.pdf',
        inscription: '/contracts/inscription.pdf',
    },
    options: {
        genders: {
            M: 'Masculino',
            F: 'Femenino',
        },
        documentTypes: {
            CC: 'Cédula de ciudadanía',
            TI: 'Tarjeta de identidad',
        },
        bloodTypes: {
            'O+': 'O+',
            'A+': 'A+',
        },
        relationships: {
            mother: 'Madre',
            father: 'Padre',
        },
        jornada: {
            morning: 'Mañana',
            afternoon: 'Tarde',
        },
    },
    recaptcha: {
        enabled: false,
        action: 'inscriptions',
    },
};

const buildProps = (overrides = {}) => ({
    ...defaultProps,
    ...overrides,
    school: {
        ...defaultSchool,
        ...(overrides.school ?? {}),
    },
    endpoints: {
        ...defaultProps.endpoints,
        ...(overrides.endpoints ?? {}),
    },
    assets: {
        ...defaultProps.assets,
        ...(overrides.assets ?? {}),
    },
    contracts: {
        ...defaultProps.contracts,
        ...(overrides.contracts ?? {}),
    },
    options: {
        ...defaultProps.options,
        ...(overrides.options ?? {}),
    },
    recaptcha: {
        ...defaultProps.recaptcha,
        ...(overrides.recaptcha ?? {}),
    },
});

const buildAutocompleteResponse = () => ({
    data: {
        data: {
            school: ['Colegio Demo'],
            place_birth: ['Bogota'],
            neighborhood: ['Centro'],
            eps: ['Sura'],
        },
    },
});

const defaultGetImplementation = (props) => (url) => {
    if (url === props.endpoints.autocomplete) {
        return Promise.resolve(buildAutocompleteResponse());
    }

    return Promise.resolve({ data: { data: {} } });
};

const mountModal = async (props = {}, options = {}) => {
    const resolvedProps = buildProps(props);

    apiMock.get.mockImplementation(options.getImplementation ?? defaultGetImplementation(resolvedProps));

    if (options.postImplementation) {
        apiMock.post.mockImplementation(options.postImplementation);
    } else {
        apiMock.post.mockResolvedValue(options.postResponse ?? { data: { data: {} } });
    }

    const wrapper = mount(PortalSchoolInscriptionModal, {
        attachTo: document.body,
        props: resolvedProps,
    });

    wrappers.push(wrapper);
    await flushPromises();

    return { wrapper, props: resolvedProps };
};

const setFieldValue = async (wrapper, name, value) => {
    const field = wrapper.get(`[name="${name}"]`);
    await field.setValue(value);
    await flushPromises();
};

const setFileValue = async (wrapper, name, file) => {
    const input = wrapper.get(`input[name="${name}"]`);

    Object.defineProperty(input.element, 'files', {
        configurable: true,
        value: [file],
    });

    await input.trigger('change');
    await flushPromises();
};

const clickAction = async (wrapper, label) => {
    const action = wrapper
        .findAll('.actions a')
        .find((link) => link.text().trim() === label);

    expect(action, `No se encontró la acción "${label}"`).toBeTruthy();

    await action.trigger('click');
    await flushPromises();
    await flushPromises();
};

const setWizardStep = async (wrapper, index) => {
    const wizard = wrapper.getComponent({ name: 'Wizard' });
    wizard.vm.$emit('update:modelValue', index);
    await flushPromises();
};

const fillPlayerStep = async (wrapper) => {
    await setFieldValue(wrapper, 'identification_document', '1234567');
    await setFieldValue(wrapper, 'document_type', 'CC');
    await setFieldValue(wrapper, 'date_birth', '2010-01-01');
    await setFieldValue(wrapper, 'names', 'Jugador');
    await setFieldValue(wrapper, 'last_names', 'Demo');
    await setFieldValue(wrapper, 'place_birth', 'Bogota');
    await setFieldValue(wrapper, 'gender', 'M');
    await setFieldValue(wrapper, 'email', 'JUGADOR@EXAMPLE.COM');
    await setFieldValue(wrapper, 'mobile', '3001234567');
};

const fillGeneralStep = async (wrapper) => {
    await setFieldValue(wrapper, 'address', 'Calle 123');
    await setFieldValue(wrapper, 'municipality', 'Bogota');
    await setFieldValue(wrapper, 'neighborhood', 'Centro');
    await setFieldValue(wrapper, 'rh', 'O+');
    await setFieldValue(wrapper, 'eps', 'Sura');
    await setFieldValue(wrapper, 'school', 'Colegio Demo');
    await setFieldValue(wrapper, 'degree', '7');
    await setFieldValue(wrapper, 'jornada', 'morning');
};

const fillFamilyStep = async (wrapper) => {
    await setFieldValue(wrapper, 'tutor_name', 'Acudiente Demo');
    await setFieldValue(wrapper, 'tutor_num_doc', '90123456');
    await setFieldValue(wrapper, 'tutor_relationship', 'mother');
    await setFieldValue(wrapper, 'tutor_phone', '3009876543');
    await setFieldValue(wrapper, 'tutor_work', 'Empresa Demo');
    await setFieldValue(wrapper, 'tutor_position_held', 'Coordinadora');
    await setFieldValue(wrapper, 'tutor_email', 'ACUDIENTE@EXAMPLE.COM');
};

const fillRequiredBaseSteps = async (wrapper) => {
    await fillPlayerStep(wrapper);
    await setWizardStep(wrapper, 1);
    await fillGeneralStep(wrapper);
    await setWizardStep(wrapper, 2);
    await fillFamilyStep(wrapper);
};

const submitVisibleWizard = async (wrapper) => {
    const wizardOptions = wrapper.getComponent({ name: 'Wizard' }).props('options');

    expect(await wizardOptions.onFinishing()).toBe(true);
    await wizardOptions.onFinished();
    await flushPromises();
};

describe('PortalSchoolInscriptionModal', () => {
    let swalFireMock;
    let bootstrapHideMock;
    let reloadMock;

    beforeEach(() => {
        apiMock.get.mockReset();
        apiMock.post.mockReset();
        apiMock.interceptors.request.use.mockReset();
        recaptchaLoadedMock.mockReset();
        executeRecaptchaMock.mockReset();

        swalFireMock = vi.fn();
        bootstrapHideMock = vi.fn();
        reloadMock = vi.fn();

        Object.defineProperty(window, '__APP_CONFIG__', {
            configurable: true,
            value: {
                appName: 'Golapp Test',
                recaptchaSiteKey: 'test-site-key',
            },
        });
        Object.defineProperty(window, 'Swal', {
            configurable: true,
            value: {
                fire: swalFireMock,
            },
        });
        Object.defineProperty(window, 'bootstrap', {
            configurable: true,
            value: {
                Modal: {
                    getInstance: vi.fn(() => null),
                    getOrCreateInstance: vi.fn(() => ({
                        hide: bootstrapHideMock,
                    })),
                },
            },
        });
        Object.defineProperty(window, 'SignaturePad', {
            configurable: true,
            value: class SignaturePad {
                constructor() {
                    this.empty = false;
                }

                isEmpty() {
                    return this.empty;
                }

                toDataURL() {
                    return 'data:image/png;base64,signature-test';
                }

                fromDataURL() {
                    this.empty = false;
                }

                clear() {
                    this.empty = true;
                }
            },
        });

        Object.defineProperty(window, 'location', {
            configurable: true,
            value: {
                ...window.location,
                reload: reloadMock,
            },
        });
    });

    afterEach(() => {
        wrappers.splice(0).forEach((wrapper) => wrapper.unmount());
        delete window.__APP_CONFIG__;
        delete window.Swal;
        delete window.bootstrap;
        delete window.SignaturePad;
        vi.useRealTimers();
    });

    it('muestra solo los pasos base cuando la escuela no exige contrato ni documentos', async () => {
        const { wrapper } = await mountModal();

        const stepTitles = wrapper.findAll('.steps li').map((step) => step.text());

        expect(stepTitles).toHaveLength(3);
        expect(stepTitles.join(' ')).toContain('Información Del Deportista');
        expect(stepTitles.join(' ')).toContain('Información general');
        expect(stepTitles.join(' ')).toContain('Información Familiar');
        expect(stepTitles.join(' ')).not.toContain('T y C');
        expect(stepTitles.join(' ')).not.toContain('Documentos');
    });

    it('agrega los pasos opcionales cuando la escuela pide contrato y documentos', async () => {
        const { wrapper } = await mountModal({
            school: {
                create_contract: true,
                send_documents: true,
                sign_player: true,
            },
        });

        const stepTitles = wrapper.findAll('.steps li').map((step) => step.text());

        expect(stepTitles).toHaveLength(5);
        expect(stepTitles.join(' ')).toContain('T y C');
        expect(stepTitles.join(' ')).toContain('Documentos');
    });

    it('restaura datos guardados y persiste solo campos serializables', async () => {
        vi.useFakeTimers();

        localStorage.setItem(defaultProps.storageKey, JSON.stringify({
            names: 'Persistido',
            email: 'USUARIO@MAIL.COM',
            mobile: '3000000000',
        }));

        const { wrapper } = await mountModal();

        expect(wrapper.get('input[name="names"]').element.value).toBe('Persistido');
        expect(wrapper.get('input[name="email"]').element.value).toBe('usuario@mail.com');

        await setFieldValue(wrapper, 'names', 'Actualizado');

        vi.advanceTimersByTime(300);
        await flushPromises();

        const persistedValues = JSON.parse(localStorage.getItem(defaultProps.storageKey));

        expect(persistedValues.names).toBe('Actualizado');
        expect(persistedValues.email).toBe('usuario@mail.com');
        expect(persistedValues).not.toHaveProperty('photo');
        expect(persistedValues).not.toHaveProperty('signatureTutor');
    });

    it('consulta el documento y rellena los datos del deportista', async () => {
        vi.useFakeTimers();

        const getImplementation = (props) => (url) => {
            if (url === props.endpoints.autocomplete) {
                return Promise.resolve(buildAutocompleteResponse());
            }

            if (url === props.endpoints.searchDoc) {
                return Promise.resolve({
                    data: {
                        data: {
                            names: 'Juan',
                            last_names: 'Perez',
                            date_birth: '2010-05-10T00:00:00.000000Z',
                            place_birth: 'Bogota',
                            document_type: 'CC',
                            gender: 'M',
                            email: 'JUAN@MAIL.COM',
                            mobile: '3001112233',
                            medical_history: 'Ninguno',
                            address: 'Calle 1',
                            municipality: 'Bogota',
                            neighborhood: 'Centro',
                            rh: 'O+',
                            eps: 'Sura',
                            student_insurance: 'Mapfre',
                            school: 'Colegio Demo',
                            degree: 7,
                            jornada: 'morning',
                        },
                    },
                });
            }

            return Promise.resolve({ data: { data: {} } });
        };

        const { wrapper, props } = await mountModal({}, {
            getImplementation: getImplementation(buildProps()),
        });

        await setFieldValue(wrapper, 'identification_document', '12345678');

        vi.advanceTimersByTime(450);
        await flushPromises();

        expect(apiMock.get).toHaveBeenCalledWith(props.endpoints.searchDoc, {
            params: {
                doc: '12345678',
                school_id: props.school.id,
            },
        });
        expect(wrapper.get('input[name="names"]').element.value).toBe('Juan');
        expect(wrapper.get('input[name="last_names"]').element.value).toBe('Perez');
        expect(wrapper.get('input[name="date_birth"]').element.value).toBe('2010-05-10');
        expect(wrapper.get('input[name="email"]').element.value).toBe('juan@mail.com');
    });

    it('envía la inscripción con el payload normalizado cuando el wizard finaliza bien', async () => {
        swalFireMock.mockResolvedValue({ isConfirmed: true });

        const { wrapper, props } = await mountModal();

        await fillRequiredBaseSteps(wrapper);
        await submitVisibleWizard(wrapper);

        expect(swalFireMock).toHaveBeenCalledWith(expect.objectContaining({
            text: '¿Deseas enviar el formulario y crear una inscripción?',
            icon: 'warning',
        }));
        expect(apiMock.post).toHaveBeenCalledTimes(1);

        const [storeUrl, formData, requestConfig] = apiMock.post.mock.calls[0];

        expect(storeUrl).toBe(props.endpoints.store);
        expect(requestConfig).toEqual({
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        expect(formData).toBeInstanceOf(FormData);
        expect(formData.get('email')).toBe('jugador@example.com');
        expect(formData.get('tutor_email')).toBe('acudiente@example.com');
        expect(formData.get('year')).toBe(String(props.year));
        expect(formData.get('contrato_insc')).toBeNull();
        expect(formData.get('g-recaptcha-response')).toBeNull();
        expect(bootstrapHideMock).toHaveBeenCalledTimes(1);
        expect(reloadMock).toHaveBeenCalledTimes(1);
        expect(localStorage.getItem(props.storageKey)).toBeNull();
        expect(swalFireMock).toHaveBeenLastCalledWith(expect.objectContaining({
            icon: 'success',
        }));
    });

    it('muestra errores del backend y regresa al paso que contiene el primer campo inválido', async () => {
        swalFireMock.mockResolvedValue({ isConfirmed: true });

        const { wrapper } = await mountModal({}, {
            postImplementation: vi.fn().mockRejectedValue({
                response: {
                    data: {
                        message: 'Revisa la información enviada.',
                        errors: {
                            address: ['La dirección no es válida.'],
                            tutor_email: ['El correo del acudiente ya existe.'],
                        },
                    },
                },
            }),
        });

        await fillRequiredBaseSteps(wrapper);
        await submitVisibleWizard(wrapper);

        expect(apiMock.post).toHaveBeenCalledTimes(1);
        expect(wrapper.getComponent({ name: 'Wizard' }).props('modelValue')).toBe(1);
        expect(wrapper.text()).toContain('Revisa la información enviada.');
        expect(wrapper.text()).toContain('La dirección no es válida.');
        expect(bootstrapHideMock).not.toHaveBeenCalled();
        expect(reloadMock).not.toHaveBeenCalled();
        expect(swalFireMock).toHaveBeenLastCalledWith(expect.objectContaining({
            icon: 'error',
            text: 'Revisa la información enviada.',
        }));
    });

    it('envía contratos, firmas y documentos cuando la escuela activa los pasos opcionales', async () => {
        swalFireMock.mockResolvedValue({ isConfirmed: true });

        const { wrapper } = await mountModal({
            school: {
                create_contract: true,
                send_documents: true,
                sign_player: true,
            },
            recaptcha: {
                enabled: true,
                action: 'portal-inscription',
            },
        });

        recaptchaLoadedMock.mockResolvedValue();
        executeRecaptchaMock.mockResolvedValue('captcha-token');

        await fillRequiredBaseSteps(wrapper);
        await setWizardStep(wrapper, 3);

        await wrapper.get('canvas').trigger('mouseup');
        await wrapper.findAll('canvas')[1].trigger('mouseup');
        await setFieldValue(wrapper, 'contrato_aff', true);
        await setFieldValue(wrapper, 'contrato_insc', true);

        await setWizardStep(wrapper, 4);

        const playerDocument = new File(['player'], 'player.pdf', { type: 'application/pdf' });
        const medicalCertificate = new File(['medical'], 'medical.pdf', { type: 'application/pdf' });
        const tutorDocument = new File(['tutor'], 'tutor.pdf', { type: 'application/pdf' });
        const paymentReceipt = new File(['payment'], 'payment.pdf', { type: 'application/pdf' });

        await setFileValue(wrapper, 'player_document', playerDocument);
        await setFileValue(wrapper, 'medical_certificate', medicalCertificate);
        await setFileValue(wrapper, 'tutor_document', tutorDocument);
        await setFileValue(wrapper, 'payment_receipt', paymentReceipt);
        await submitVisibleWizard(wrapper);

        expect(recaptchaLoadedMock).toHaveBeenCalledTimes(1);
        expect(executeRecaptchaMock).toHaveBeenCalledWith('portal-inscription');

        const [, formData] = apiMock.post.mock.calls[0];

        expect(formData.get('signatureTutor')).toBe('data:image/png;base64,signature-test');
        expect(formData.get('signatureAlumno')).toBe('data:image/png;base64,signature-test');
        expect(formData.get('contrato_aff')).toBe('1');
        expect(formData.get('contrato_insc')).toBe('1');
        expect(formData.get('player_document')).toBe(playerDocument);
        expect(formData.get('medical_certificate')).toBe(medicalCertificate);
        expect(formData.get('tutor_document')).toBe(tutorDocument);
        expect(formData.get('payment_receipt')).toBe(paymentReceipt);
        expect(formData.get('g-recaptcha-response')).toBe('captcha-token');
    });
});
