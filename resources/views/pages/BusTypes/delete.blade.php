<!-- delete_modal_city -->
<div class="modal fade" id="delete{{ $item->id }}" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 style="font-family: 'Cairo', sans-serif;" class="modal-title"
                    id="exampleModalLabel">
                    حذف أسطول
                </h5>
                <button type="button" class="close" data-dismiss="modal"
                        aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('busTypes.destroy',$item->id) }}" method="post">
                    @csrf
                    @method('DELETE')
                    هل أنت متأكد من عملية الحذف
                    <input id="id" type="hidden" name="id" class="form-control" value="{{ $item->id }}">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-danger">حفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
