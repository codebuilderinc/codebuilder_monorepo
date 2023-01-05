@extends('layouts.app')

@section('content')
    <button id="enable-push">Enable push notifications</button>
    <button id="disable-push">Disable push notifications</button>

    <div class="container" style="padding-bottom: 75px;">

        <div class="row">
            <div class="col" style="padding-left: 0px; padding-right: 0px;">
                <a style="width: 100%; border-top-right-radius: 0px;  border-bottom-right-radius: 0px;  border-top-left-radius: 0px;  border-bottom-left-radius: 0px; padding: 15px; color:black;'"
                    href="/jobs" class="btn"><i class="fas fa-inbox"></i> <strong>Review</strong></a>
            </div>
            <div class="col" style="padding-left: 0px; padding-right: 0px;">
                <a style="width: 100%; border-top-right-radius: 0px;  border-bottom-right-radius: 0px;  border-top-left-radius: 0px;  border-bottom-left-radius: 0px; padding: 15px; color:green;'"
                    href="/jobs/messaged" class="btn"><i class="fas fa-envelope-open-text  "></i>
                    <strong>Messaged</strong></a>
            </div>
            <div class="col" style="padding-left: 0px; padding-right: 0px;">
                <a style="width: 100%; border-top-right-radius: 0px;  border-bottom-right-radius: 0px; border-top-left-radius: 0px;  border-bottom-left-radius: 0px; color: red; padding: 15px;"
                    href="/jobs/rejected" class="btn"><i class="fas fa-trash"></i> Rejected </a>
            </div>
        </div>
        <button onclick="initSW();" class="bt btn-primary">Subscribe to Notifications</button>

        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Dashboard</div>

                    <div class="panel-body text-center">
                        {{-- See resources/assets/js/components/NotificationsDemo.vue --}}
                        <notifications-demo></notifications-demo>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach ($jobs as $job)
                <div class="" style="width: 100%;">
                    <div class="col justify-content-center"
                        style="padding-left: 0px; padding-right: 0px; border-bottom: 0px; margin-bottom: 10px;">
                        <div class="card" style="min-width: 200px; width: 100%;">
                            <div class="card-body" style="padding-left: 0px; padding-right: 0px; padding-bottom: 0px;">
                                <div style="padding: 1.25em; padding-top: 0px;">
                                    <h5 class="card-title"><a href="https://reddit.com/{{ $job->href }}"
                                            target="_blank">{{ $job->post_title }}</a></h5>
                                    <hr>
                                    <small class="text-muted">Posted by <a
                                            href="httpS://reddit.com/user/{{ $job->post_author }}">{{ $job->post_author }}</a>
                                        &middot; {{ Carbon\Carbon::parse($job->created_at)->diffForHumans() }} on <a
                                            href="https://reddit.com/r/{{ $job->subreddit }}"
                                            target="_blank">{{ $job->subreddit }}</a> </small>
                                    <p class="card-text">{!! html_entity_decode($job->post_body) !!}</p>
                                </div>
                                <div class="container">
                                    <div class="row">
                                        <div class="col" style="padding-left: 0px; padding-right: 0px;">


                                            @if ($job->status == '')
                                                <form action="/job/reject/{{ $job->id }}" method="post">
                                                    {{ csrf_field() }}
                                                    <button type="submit"
                                                        style="width: 100%; border-top-right-radius: 0px;  border-bottom-right-radius: 0px; border-top-left-radius: 0px;  border-bottom-left-radius: 0px;"
                                                        href="#" class="btn btn-danger"><i class="fas fa-trash"></i>
                                                        Reject </button>
                                                </form>
                                            @endif
                                            @if ($job->status == 'rejected')
                                                <form action="/job/restore/{{ $job->id }}" method="post">
                                                    {{ csrf_field() }}
                                                    <button type="submit"
                                                        style="width: 100%; border-top-right-radius: 0px;  border-bottom-right-radius: 0px; border-top-left-radius: 0px;  border-bottom-left-radius: 0px;"
                                                        href="#" class="btn btn-success"><i class="fas fa-check"></i>
                                                        Restore </button>
                                                </form>
                                            @endif
                                        </div>
                                        @if ($job->status == '')
                                            <div class="col" style="padding-left: 0px; padding-right: 0px;">
                                                <a style="width: 100%; border-top-right-radius: 0px;  border-bottom-right-radius: 0px;  border-top-left-radius: 0px;  border-bottom-left-radius: 0px;"
                                                    href="/job/message/{{ $job->id }}" class="btn btn-primary"><i
                                                        class="fas fa-envelope"></i> Message</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            @if (!count($jobs))
                <div class="" style="width: 100%; height: 100%; text-align: center; padding-top: 130px;">
                    <h3>No jobs to show. <i class="fa fa-thumbs-up"></i></h3>
                </div>
            @endif
        </div>
        <script src="/main.js" defer></script>


    </div>
@endsection
