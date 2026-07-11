<?php

namespace App\Service\Notification;

use App\Models\Inscription;
use App\Models\TopicNotification;
use App\Notifications\FirebaseTopicNotification;
use App\Repositories\TopicNotificationRepository;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class TopicNotificationStoreService
{
    public function __construct(
        private TopicNotificationRepository $repository
    ) {
        //
    }

    public function store(array $data): TopicNotification
    {
        [$topics, $playerIds] = $this->getTopicsAndPlayers($data);

        return $this->saveNotification(
            data: $data,
            topics: $topics,
            playerIds: $playerIds
        );
    }

    public function storeForTopics(array $data, array $topics, array $playerIds): TopicNotification
    {
        return $this->saveNotification($data, $topics, $playerIds);
    }

    private function saveNotification(
        array $data,
        array $topics,
        array $playerIds
    ): TopicNotification {
        try {
            $topicNotification = DB::transaction(
                function () use ($data, $topics, $playerIds) {
                    $topicNotification = TopicNotification::query()->create([
                        'school_id' => $data['school_id'],
                        'topics' => implode(',', $topics),
                        'title' => $data['notification_title'],
                        'body' => $data['notification_body'],
                        'type' => 'GENERAL',
                        'priority' => 'NORMAL',
                    ]);

                    $uniquePlayerIds = array_values(
                        array_unique($playerIds)
                    );

                    if (!empty($uniquePlayerIds)) {
                        $topicNotification->players()->attach(
                            $uniquePlayerIds,
                            [
                                'school_id' => $data['school_id'],
                                'is_read' => false,
                            ]
                        );
                    }

                    return $topicNotification;
                }
            );

            /*
             * El envío se realiza después de confirmar la transacción.
             * Así Firebase no recibe la notificación antes de que exista
             * el registro en la base de datos.
             */
            Notification::send(
                new AnonymousNotifiable(),
                FirebaseTopicNotification::create()
                    ->toTopics($topics)
                    ->withData([
                        'action' => 'sync_notifications',
                    ])
            );

            return $topicNotification;
        } catch (\Throwable $exception) {
            report($exception);

            /*
             * No se debe ocultar el error, porque el controlador podría
             * responder como si la notificación se hubiera guardado.
             */
            throw $exception;
        }
    }

    private function getTopicsAndPlayers(array $data): array
    {
        $school = getSchool(auth()->user());
        $selectedPlayerIds = array_map(
            'intval',
            $data['players'] ?? []
        );

        [
            $topicCategories,
            $topicGroups,
            $topicUniqueCodes,
            $topicCompetitionGroups,
        ] = $this->repository->getTopics();

        $type = $data['notification_type'];

        /*
         * Los topics deben salir de la lista correspondiente al tipo,
         * no del valor de notification_type.
         */
        $topics = match ($type) {
            'general' => [
                TopicService::generateTopic(
                    'general',
                    $school->slug
                ),
            ],

            'categories' => $data['categories'] ?? [],

            'training_groups' => $data['training_groups'] ?? [],

            'competition_groups' => $data['competition_groups'] ?? [],

            'players' => collect($topicUniqueCodes)
                ->filter(
                    fn (array $playerTopic) => in_array(
                        (int) $playerTopic['search'],
                        $selectedPlayerIds,
                        true
                    )
                )
                ->pluck('topic')
                ->all(),

            default => throw new \RuntimeException(
                "El tipo de notificación '{$type}' no existe."
            ),
        };

        $topics = array_values(
            array_unique(
                array_filter($topics)
            )
        );

        if (empty($topics)) {
            throw new \RuntimeException(
                "No se encontraron topics para el tipo '{$type}'."
            );
        }

        $source = match ($type) {
            'general' => [],

            'categories' => $topicCategories,

            'training_groups' => $topicGroups,

            'competition_groups' => $topicCompetitionGroups,

            'players' => $topicUniqueCodes,

            default => [],
        };

        $searches = $type === 'general'
            ? []
            : $this->getSearch(
                $source,
                $type === 'players' ? $selectedPlayerIds : $topics
            );

        /*
         * Esto permite detectar topics que pasaron desde el frontend
         * pero que el repositorio no pudo resolver.
         */
        if ($type !== 'general' && empty($searches)) {
            throw new \RuntimeException(
                'Ninguno de los topics seleccionados pudo ser relacionado con registros de la escuela.'
            );
        }

        if ($type === 'players' && count($searches) !== count(array_unique($selectedPlayerIds))) {
            throw new \RuntimeException(
                'Uno o más jugadores seleccionados no pertenecen a la escuela o no tienen una inscripción vigente.'
            );
        }

        $playerIds = $this->getPlayerIds(
            type: $type,
            search: $searches
        );

        return [$topics, $playerIds];
    }

    private function getSearch(array $source, array $topics): array
    {
        $isPlayerSelection = collect($topics)->every(
            fn ($topic) => is_int($topic)
        );

        return collect($source)
            ->filter(function (array $item) use ($topics, $isPlayerSelection) {
                $candidate = $isPlayerSelection
                    ? (int) $item['search']
                    : $item['topic'];

                return in_array($candidate, $topics, true);
            })
            ->pluck('search')
            ->filter(
                fn ($value) => $value !== null && $value !== ''
            )
            ->unique()
            ->values()
            ->all();
    }

    private function getPlayerIds(
        string $type,
        array $search
    ): array {
        /*
         * Se filtra por el año vigente para evitar vincular jugadores
         * de inscripciones históricas.
         */
        $query = Inscription::query()
            ->schoolId()
            ->where('year', now()->year);

        $query = match ($type) {
            'general' => $query,

            'categories' => $query->whereIn(
                'category',
                $search
            ),

            'training_groups' => $query->whereIn(
                'training_group_id',
                $search
            ),

            'competition_groups' => $query->whereHas(
                'competitionGroup',
                fn ($competitionGroupQuery) =>
                    $competitionGroupQuery->whereIn(
                        'competition_groups.id',
                        $search
                    )
            ),

            'players' => $query->whereIn(
                'player_id',
                $search
            ),

            default => throw new \RuntimeException(
                "El tipo de notificación '{$type}' no existe."
            ),
        };

        return $query
            ->whereNotNull('player_id')
            ->distinct()
            ->pluck('player_id')
            ->map(fn ($playerId) => (int) $playerId)
            ->values()
            ->all();
    }
}
