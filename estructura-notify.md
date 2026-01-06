1. CAPA PRESENTACIÓN (features)

app/
├── feature/
│   ├── auth/              # Módulo de autenticación
│   │   ├── presentation/
│   │   │   ├── screens/
│   │   │   │   ├── LoginScreen.kt
│   │   │   │   ├── RegisterScreen.kt
│   │   │   │   └── ForgotPasswordScreen.kt
│   │   │   └── viewmodels/
│   │   │       └── AuthViewModel.kt
│   │   ├── domain/
│   │   │   ├── models/
│   │   │   │   └── User.kt
│   │   │   ├── usecases/
│   │   │   │   ├── LoginUseCase.kt
│   │   │   │   └── RegisterUseCase.kt
│   │   │   └── repository/
│   │   │       └── AuthRepository.kt
│   │   └── data/
│   │       └── repository/
│   │           └── AuthRepositoryImpl.kt
│   │
│   ├── notifications/     # Módulo de notificaciones
│   │   ├── presentation/
│   │   │   ├── screens/
│   │   │   │   ├── NotificationsScreen.kt
│   │   │   │   └── NotificationDetailScreen.kt
│   │   │   └── viewmodels/
│   │   │       └── NotificationsViewModel.kt
│   │   ├── domain/
│   │   │   ├── models/
│   │   │   │   └── Notification.kt
│   │   │   ├── usecases/
│   │   │   │   ├── GetNotificationsUseCase.kt
│   │   │   │   ├── MarkAsReadUseCase.kt
│   │   │   │   └── SubscribeToTopicUseCase.kt
│   │   │   └── repository/
│   │   │       └── NotificationsRepository.kt
│   │   └── data/
│   │       └── repository/
│   │           └── NotificationsRepositoryImpl.kt
│   │
│   ├── requests/          # Módulo de solicitudes de implementos
│   │   ├── presentation/
│   │   │   ├── screens/
│   │   │   │   ├── RequestsScreen.kt
│   │   │   │   ├── CreateRequestScreen.kt
│   │   │   │   └── RequestDetailScreen.kt
│   │   │   └── viewmodels/
│   │   │       └── RequestsViewModel.kt
│   │   ├── domain/
│   │   │   ├── models/
│   │   │   │   ├── EquipmentRequest.kt
│   │   │   │   ├── EquipmentType.kt
│   │   │   │   └── RequestStatus.kt
│   │   │   ├── usecases/
│   │   │   │   ├── CreateRequestUseCase.kt
│   │   │   │   ├── GetRequestsUseCase.kt
│   │   │   │   └── UpdateRequestUseCase.kt
│   │   │   └── repository/
│   │   │       └── RequestsRepository.kt
│   │   └── data/
│   │       └── repository/
│   │           └── RequestsRepositoryImpl.kt
│   │
│   ├── payments/          # Módulo de pagos con imágenes
│   │   ├── presentation/
│   │   │   ├── screens/
│   │   │   │   ├── PaymentsScreen.kt
│   │   │   │   ├── UploadPaymentScreen.kt
│   │   │   │   └── PaymentHistoryScreen.kt
│   │   │   └── viewmodels/
│   │   │       └── PaymentsViewModel.kt
│   │   ├── domain/
│   │   │   ├── models/
│   │   │   │   └── Payment.kt
│   │   │   ├── usecases/
│   │   │   │   ├── UploadPaymentUseCase.kt
│   │   │   │   └── GetPaymentsUseCase.kt
│   │   │   └── repository/
│   │   │       └── PaymentsRepository.kt
│   │   └── data/
│   │       └── repository/
│   │           └── PaymentsRepositoryImpl.kt
│   │
│   └── profile/           # Módulo de perfil
│       ├── presentation/
│       │   ├── screens/
│       │   │   └── ProfileScreen.kt
│       │   └── viewmodels/
│       │       └── ProfileViewModel.kt
│       ├── domain/
│       │   ├── models/
│       │   ├── usecases/
│       │   │   ├── GetProfileUseCase.kt
│       │   │   └── UpdateProfileUseCase.kt
│       │   └── repository/
│       │       └── ProfileRepository.kt
│       └── data/
│           └── repository/
│               └── ProfileRepositoryImpl.kt


2. CAPA CORE (compartida)
app/
├── core/
│   ├── common/            # Utilidades comunes
│   │   ├── constants/
│   │   │   ├── AppConstants.kt
│   │   │   └── PreferenceKeys.kt
│   │   ├── extensions/
│   │   │   ├── StringExtensions.kt
│   │   │   ├── ViewExtensions.kt
│   │   │   └── DateExtensions.kt
│   │   ├── utils/
│   │   │   ├── Validators.kt
│   │   │   ├── Formatters.kt
│   │   │   └── NetworkUtils.kt
│   │   └── Result.kt     # Clase sealed para resultados
│   │
│   ├── di/                # Inyección de dependencias
│   │   ├── AppModule.kt
│   │   ├── DatabaseModule.kt
│   │   ├── NetworkModule.kt
│   │   └── ViewModelModule.kt
│   │
│   ├── navigation/        # Navegación
│   │   ├── Destinations.kt
│   │   ├── NavGraph.kt
│   │   └── NavigationViewModel.kt
│   │
│   └── theme/            # Diseño y temas
│       ├── Theme.kt
│       ├── Colors.kt
│       ├── Typography.kt
│       └── components/   # Componentes reutilizables
│           ├── buttons/
│           ├── cards/
│           ├── dialogs/
│           └── forms/

3. CAPA DE DATOS
app/
├── data/
│   ├── local/            # Datos locales
│   │   ├── database/
│   │   │   ├── AppDatabase.kt
│   │   │   ├── entities/
│   │   │   │   ├── UserEntity.kt
│   │   │   │   ├── RequestEntity.kt
│   │   │   │   └── NotificationEntity.kt
│   │   │   └── daos/
│   │   │       ├── UserDao.kt
│   │   │       ├── RequestDao.kt
│   │   │       └── NotificationDao.kt
│   │   └── datastore/
│   │       └── AppDataStore.kt
│   │
│   ├── remote/           # Datos remotos (APIs)
│   │   ├── api/
│   │   │   ├── ApiService.kt
│   │   │   ├── AuthApi.kt
│   │   │   ├── NotificationsApi.kt
│   │   │   ├── RequestsApi.kt
│   │   │   └── PaymentsApi.kt
│   │   ├── models/
│   │   │   ├── requests/
│   │   │   │   ├── LoginRequest.kt
│   │   │   │   └── CreateRequestRequest.kt
│   │   │   └── responses/
│   │   │       ├── AuthResponse.kt
│   │   │       ├── ApiResponse.kt
│   │   │       └── ErrorResponse.kt
│   │   └── interceptors/
│   │       ├── AuthInterceptor.kt
│   │       └── LoggingInterceptor.kt
│   │
│   └── repositories/     # Repositorios (implementaciones)
│       ├── AuthRepositoryImpl.kt
│       ├── NotificationsRepositoryImpl.kt
│       ├── RequestsRepositoryImpl.kt
│       └── PaymentsRepositoryImpl.kt