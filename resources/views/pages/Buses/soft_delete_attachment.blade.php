<!-- Deleted inFormation Student -->
<div class="modal fade" id="soft_delete_img{{$attachment->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 style="font-family: 'Cairo', sans-serif;" class="modal-title" id="exampleModalLabel">حذف مرفق</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('soft_delete_bus_attachment')}}" method="get">
                    @csrf
                    <input type="hidden" name="id" value="{{$attachment->id}}">
                    <input type="hidden" name="bus_id" value="{{$attachment->bus->id}}">

                    <h5 style="font-family: 'Cairo', sans-serif;">هل أنت متأكد من حذف المرفق ؟</h5>
                    <h5 style="font-family: 'Cairo', sans-serif;">سيتم نقل المرفق إلى سلة المهملات .</h5>
                    <input type="text" name="name" readonly value="{{$attachment->name}}" class="form-control">
                    <input type="hidden" name="fileName" readonly value="{{$attachment->fileName}}" class="form-control">

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                        <button  class="btn btn-danger">حفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
