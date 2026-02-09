<?php

namespace App\Service\Notification;

use App\Models\Inscription;
use App\Models\TopicNotification;
use App\Notifications\FirebaseTopicNotification;
use App\Repositories\TopicNotificationRepository;
use App\Traits\ErrorTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class TopicNotificationStoreService
{
    use ErrorTrait;

    public function __construct(private TopicNotificationRepository $repository)
    {
        //
    }

    public function store(FormRequest $request)
    {
        [$topics, $playerIds] = $this->getTopicsAndPlayers($request);

        try {
            DB::transaction(function() use($request, $topics, $playerIds){
                    $tipicNotification = TopicNotification::query()->create([
                    'school_id' => $request->validated('school_id'),
                    'topics'  => implode(',', $topics),
                    'title'  => $request->validated('notification_title'),
                    'body'  => $request->validated('notification_body'),
                    'type' => 'GENERAL',
                    'priority' => 'NORMAL'
                ]);

                $tipicNotification->players()->attach($playerIds, [
                    'school_id' => $request->validated('school_id'),
                    'is_read' => false
                ]);

                // envio de topics
                Notification::send(
                    new AnonymousNotifiable(),
                    FirebaseTopicNotification::create()
                        ->toTopics($topics)
                        ->withData([// para sincronizar notificaciones ['action' => 'sync_notifications']
                            'action' => 'sync_notifications'
                        ])
                );
            });


        } catch (\Throwable $th) {
            $this->logError('TopicNotificationStoreService@store', $th);
        } finally {
            unset($playerIds, $topics);
        }
    }

    private function getTopicsAndPlayers(FormRequest $request)
    {
        $topics = [];
        $searchs = [];
        $source = [];
        $school = getSchool(auth()->user());
        [$topicCategories, $topicGroups, $topicUniqueCodes, $topicCompetitionGroups] = $this->repository->getTopics();

        $type = $request->validated('notification_type');
        $topics = $request->validated($type, []);

        switch ($type) {
            case 'general':
                $topics[] = TopicService::generateTopic('general', $school->slug);
                break;
            case 'categories':
                $source = $topicCategories;
                break;
            case 'training_groups':
                $source = $topicGroups;
                break;
            case 'competition_groups':
                $source = $topicCompetitionGroups;
                break;
            case 'players':
                $source = $topicUniqueCodes;
                break;
            default:
                throw new \Exception('tipo de notificacion no existe ' . $type);
        }

        $searchs = $this->getSearch($source, $request->validated($type, []), );

        $playerIds = $this->getPlayerIds($type, $searchs);

        unset($searchs);

        return [$topics, $playerIds];
    }

    private function getSearch(array $source, array $topics): array
    {
        return collect($source)
            ->whereIn('topic', $topics, true)
            ->map(fn($item) => $item['search'])->toArray();
    }

    private function getPlayerIds(string $type, array $search): array
    {
        $query = Inscription::query()->schoolId();
        $ids = [];
        switch ($type) {
            case 'general':
                $ids = $query->where('year', now()->year)->pluck('player_id')->toArray();
                break;
            case 'categories':
                $ids = $query->whereIn('category', $search)->pluck('player_id')->toArray();
                break;
            case 'training_groups':
                $ids = $query->whereIn('training_group_id', $search)->pluck('player_id')->toArray();
                break;
            case 'competition_groups':
                $ids = $query->whereHas('competitionGroup', fn($q) => $q->whereIn('competition_groups.id', $search))->pluck('player_id')->toArray();
                break;
            case 'players':
                $ids = $query->whereIn('unique_code', $search)->pluck('player_id')->toArray();
                break;
            default:
                throw new \Exception('tipo de notificacion no existe ' . $type);
        }
        return $ids;
    }
}
