@extends('layouts.app')
@php
  /**
   * @var \App\Models\Solution[]|\Illuminate\Support\Collection $solutions
   */
@endphp
@section('content')
  <div class="my-4">
    <section>

      @include('exercise.navigation')

      <h1 class="h3">{{ __('views.solution.index.header.h1') }}</h1>

      {{ html()->form('GET', route('solutions.index'))->class('my-3 row row-cols-lg-auto g-3 align-items-center')->open() }}
      <div class="col">
        <div class="input-group">
          <div class="input-group-text">{{ __('views.solution.index.filter.user') }}</div>
          {{ html()->text('filter[user.name]')->value(Arr::get($filter, 'user.name', null))->placeholder(__('views.solution.index.filter.user'))->class('form-control') }}
        </div>
      </div>
      <div class="col-6 col-sm-6 col-md-4 col-lg-4">
        <div class="input-group">
          <div class="input-group-text">{{ __('views.solution.index.filter.exercise') }}</div>
          {{ html()->select('filter[exercise_id]', $exerciseTitles)->value(Arr::get($filter, 'exercise_id', null))->placeholder(__('views.solution.index.filter.exercise'))->class('form-control') }}
        </div>
      </div>
      <div class="col-6 col-sm-3 col-md-2 col-lg-2 d-flex justify-content-center">
        {{ html()->submit(__('views.solution.index.filter.apply_button'))->class('btn btn-outline-primary') }}
      </div>
      <div class="col-6 col-sm-3 col-md-2 col-lg-2 d-flex justify-content-center">
        {{ html()->a(route('solutions.index'), __('views.solution.index.filter.reset_button'))->class('btn btn-outline-secondary ml-3') }}
      </div>
      {{ html()->form()->close() }}

      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <td>{{ __('views.solution.index.table_header.author') }}</td>
              <td>{{ __('views.solution.index.table_header.exercise') }}</td>
              <td>{{ __('views.solution.index.table_header.date') }}</td>
              <td></td>
            </tr>
          </thead>
          <tbody>
            @foreach ($solutions as $solution)
              <tr>
                <td>
                  <a class="text-decoration-none" href="{{ route('users.show', $solution->user) }}">
                    <img class="rounded-circle mr-1" width="30" height="30"
                      src="{{ $solution->user->present()->getProfileImageLink() }}" alt="Profile image">
                    {{ $solution->user->name }}
                  </a>
                </td>
                <td>
                  <a href="{{ route('exercises.show', $solution->exercise) }}">
                    {{ $solution->exercise->getFullTitle() }}
                  </a>
                </td>
                <td>{{ $solution->created_at }}</td>
                <td>
                  <a href="{{ route('solutions.show', $solution) }}">
                    {{ __('views.solution.index.show_action') }}
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      {{ $solutions->withQueryString()->links() }}
    </section>
  </div>
@endsection
