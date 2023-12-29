@if($data)
    @php
                // need to recreate object because policy might depend on record data
                $class = get_class($action);
                $action = new $class($dataType, $data);
    @endphp
    @can ($action->getPolicy(), $data)
        @if ($action->shouldActionDisplayOnRow($data))
            @if($action instanceof \TCG\Voyager\Actions\ViewAction and $dataType->name === 'invoices' and isset($data->id))
                <a target="_blank" href="{{ route('invoices.get', ['id' => $data->id]) }}" title="{{ $action->getTitle() }}" {!! $action->convertAttributesToHtml() !!}>
                    <i class="{{ $action->getIcon() }}"></i> <span class="hidden-xs hidden-sm">{{ $action->getTitle() }}</span>
                </a>
            @else
                @if($action instanceof \TCG\Voyager\Actions\ViewAction and $dataType->name === 'users' and isset($data->id) && Auth::user()->role_id === 1)
                    <a class="impersonate btn btn-sm btn-danger pull-right view" target="_blank" href="{{ route('admin.impersonate', ['id' => $data->id]) }}" title="{{ __("Impersonate") }}">
                        <i class="voyager-person"></i> <span class="hidden-xs hidden-sm">{{ __('Login') }}</span>
                    </a>
                @endif
                <a href="{{ $action->getRoute($dataType->name) }}" title="{{ $action->getTitle() }}" {!! $action->convertAttributesToHtml() !!}>
                    <i class="{{ $action->getIcon() }}"></i> <span class="hidden-xs hidden-sm">{{ $action->getTitle() }}</span>
                </a>
            @endif
        @endif
    @endcan
@elseif (method_exists($action, 'massAction'))
    <form method="post" action="{{ route('voyager.'.$dataType->slug.'.action') }}" class="display-inline">
        {{ csrf_field() }}
        <button type="submit" {!! $action->convertAttributesToHtml() !!}><i class="{{ $action->getIcon() }}"></i>  {{ $action->getTitle() }}</button>
        <input type="hidden" name="action" value="{{ get_class($action) }}">
        <input type="hidden" name="ids" value="" class="selected_ids">
    </form>
@endif
