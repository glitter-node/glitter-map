<?php

namespace App\Providers;

use App\Support\BroadcastTrace;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Redis\Events\CommandExecuted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Reverb\Events\MessageReceived;
use Laravel\Reverb\Events\MessageSent;

class BroadcastTraceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(JobQueued::class, function (JobQueued $event): void {
            $job = is_string($event->job) ? $event->job : json_encode($event->job);
            $payload = is_string($event->payload) ? $event->payload : json_encode($event->payload);

            if (! str_contains($job, 'BroadcastEvent') && ! str_contains($payload, 'BroadcastEvent')) {
                return;
            }

            BroadcastTrace::log('queue.queued', [
                'connection' => $event->connectionName,
                'queue' => $event->queue,
                'job' => $job,
                'job_id' => $event->id,
            ]);
        });

        Event::listen(JobProcessing::class, function (JobProcessing $event): void {
            $name = $event->job->resolveName();

            if (! str_contains($name, 'BroadcastEvent')) {
                return;
            }

            BroadcastTrace::log('queue.processing', [
                'connection' => $event->connectionName,
                'queue' => $event->job->getQueue(),
                'job' => $name,
                'payload' => $event->job->payload(),
            ]);
        });

        Event::listen(JobProcessed::class, function (JobProcessed $event): void {
            $name = $event->job->resolveName();

            if (! str_contains($name, 'BroadcastEvent')) {
                return;
            }

            BroadcastTrace::log('queue.processed', [
                'connection' => $event->connectionName,
                'queue' => $event->job->getQueue(),
                'job' => $name,
            ]);
        });

        Event::listen(JobFailed::class, function (JobFailed $event): void {
            $name = $event->job->resolveName();

            if (! str_contains($name, 'BroadcastEvent')) {
                return;
            }

            BroadcastTrace::log('queue.failed', [
                'connection' => $event->connectionName,
                'queue' => $event->job->getQueue(),
                'job' => $name,
                'exception' => $event->exception->getMessage(),
            ]);
        });

        Event::listen(CommandExecuted::class, function (CommandExecuted $event): void {
            if (! in_array(strtolower($event->command), ['publish', 'spublish'], true)) {
                return;
            }

            BroadcastTrace::log('redis.publish', [
                'command' => $event->command,
                'parameters' => $event->parameters,
                'connection' => $event->connectionName,
                'time' => $event->time,
            ]);
        });

        Event::listen(MessageReceived::class, function (MessageReceived $event): void {
            BroadcastTrace::log('reverb.message_received', [
                'connection_id' => $event->connection->id(),
                'message' => (string) $event->message,
            ]);
        });

        Event::listen(MessageSent::class, function (MessageSent $event): void {
            BroadcastTrace::log('reverb.message_sent', [
                'connection_id' => $event->connection->id(),
                'message' => (string) $event->message,
            ]);
        });
    }
}
