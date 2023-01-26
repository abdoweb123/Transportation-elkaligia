<div class="container-fluid">
    <div class="row">
        <!-- Left Sidebar start-->
        <div class="side-menu-fixed">
            <div class="scrollbar side-menu-bg">
                <ul class="nav navbar-nav side-menu" id="sidebarnav">
                    <!-- menu item Dashboard-->
                    <li class="mt-10 mb-10 text-muted pl-4 font-medium menu-title">{{trans('main_trans.dashboard')}}</li>


                    <li>
                        <a href="{{route('admin.dashboard')}}" style="padding-bottom:30px;">
                            <div class="pull-left"><i class="ti-home"></i><span class="right-nav-text">لوحة التحكم</span>
                            </div>
                        </a>
                    </li>


                    <li>
                        <a href="javascript:void(0);" data-toggle="collapse" data-target="#cities">
                            <div class="pull-left"><i class="ti-layers"></i><span class="right-nav-text">قاعدة البيانات</span>
                            </div>
                            <div class="pull-right"><i class="ti-plus"></i></div>
                            <div class="clearfix"></div>
                        </a>
                        <ul id="cities" class="collapse" data-parent="#sidebarnav">
                            <li> <a href="{{route('countries.index')}}">قائمة الدول</a></li>
                            <li> <a href="{{route('states.index')}}">قائمة المحافظات</a></li>
                            <li> <a href="{{route('cities.index')}}">{{trans('main_trans.cities_list')}}</a> </li>
                            <li> <a href="{{route('stations.index')}}">{{trans('main_trans.stations_list')}}</a> </li>
                            <li> <a href="{{route('departments.index')}}">قائمة الأقسام</a> </li>
                            <li> <a href="{{route('offices.index')}}">قائمة المكاتب</a> </li>
                            <li class="mt-10 mb-10 text-muted pl-4 font-medium menu-title">الحافلات</li>
                            <li> <a href="{{route('licences.index')}}">أنواع الرخص</a></li>
                            <li> <a href="{{route('busModels.index')}}">موديل الحافلات</a></li>
                            <li> <a href="{{route('busOwners.index')}}">مٌلاك الحافلات</a></li>
                            <li> <a href="{{route('insuranceCompanies.index')}}">شركات التأمين</a></li>
                            <li> <a href="{{route('banks.index')}}">قائمة البنوك</a></li>
                            <li> <a href="{{route('busTypes.index')}}">أساطيل الحافلات</a></li>
                            <li> <a href="{{route('buses.index')}}">قائمة الحافلات</a></li>
                            <li> <a href="{{route('seats.create')}}">إضافة تصميم</a></li>
                            <li class="mt-10 mb-10 text-muted pl-4 font-medium menu-title">الكوبونات والاشتراكات</li>
                            <li> <a href="{{route('coupons.index')}}">قائمة الكوبونات</a></li>
                            <li> <a href="{{route('packages.index')}}">قائمة الاشتراكات</a></li>
                            <li> <a href="{{route('bookedPackages.index')}}">قائمة الاشتراكات المحجوزة</a></li>
                            <li> <a href="{{route('millages.index')}}">قائمة الخصومات</a></li>
                            <li class="mt-10 mb-10 text-muted pl-4 font-medium menu-title">تصنيف العملاء</li>
                            <li> <a href="{{route('customerTypes.index')}}">قائمة التصنيفات</a></li>
                        </ul>
                    </li>



{{--                    <li>--}}
{{--                        <a href="javascript:void(0);" data-toggle="collapse" data-target="#companyEmployees">--}}
{{--                            <div class="pull-left"><i class="ti-id-badge"></i><span class="right-nav-text">موظفو الشركات الخاصة</span></div>--}}
{{--                            <div class="pull-right"><i class="ti-plus"></i></div>--}}
{{--                            <div class="clearfix"></div>--}}
{{--                        </a>--}}
{{--                        <ul id="companyEmployees" class="collapse" data-parent="#sidebarnav">--}}
{{--                            <li> <a href="{{route('employeeJobs.index')}}">قائمة الوظائف</a> </li>--}}
{{--                            <li> <a href="{{route('departments.index')}}">قائمة الأقسام</a> </li>--}}
{{--                            <li> <a href="{{route('myEmployees.index')}}">قائمة المموظفين</a> </li>--}}
{{--                            <li> <a href="{{route('getExcel.excelEmployee')}}">استيراد بيانات المموظفين</a> </li>--}}
{{--                        </ul>--}}
{{--                    </li>--}}



                    <li>
                        <a href="javascript:void(0);" data-toggle="collapse" data-target="#tripData">
                            <div class="pull-left"><i class="ti-car"></i><span class="right-nav-text">الرحلات</span></div>
                            <div class="pull-right"><i class="ti-plus"></i></div>
                            <div class="clearfix"></div>
                        </a>
                        <ul id="tripData" class="collapse" data-parent="#sidebarnav">
                            <li> <a href="{{route('degrees.index')}}">قائمة درجات الرحلات</a></li>
                            <li> <a href="{{route('tripData.index')}}">قائمة الرحلات</a></li>
                            <li> <a href="{{route('runTrips.index')}}">قائمة الرحلات المشغلة</a></li>
                            <li class="mt-10 mb-10 text-muted pl-4 font-medium menu-title">الحجوزات</li>
                            <li> <a href="{{route('reservationBookingRequests.searchLines')}}">إضافة حجز</a></li>
                            <li> <a href="{{route('reservationBookingRequests.editPage')}}">تعديل حجز</a></li>
                            <li> <a href="{{route('settings.index')}}">إعدادات الحجز</a></li>
{{--                            <li> <a href="{{route('reservationBookingRequests.index')}}">قائمة الحجوزات</a></li>--}}
                        </ul>
                    </li>


{{--                    <li>--}}
{{--                        <a href="javascript:void(0);" data-toggle="collapse" data-target="#bus_settings">--}}
{{--                            <div class="pull-left"><i class="ti-settings"></i><span class="right-nav-text">إعدادات الحافلات</span></div>--}}
{{--                            <div class="pull-right"><i class="ti-plus"></i></div>--}}
{{--                            <div class="clearfix"></div>--}}
{{--                        </a>--}}
{{--                        <ul id="bus_settings" class="collapse" data-parent="#sidebarnav">--}}
{{--                            <li> <a href="{{route('vendors.index')}}">قائمة الموردين</a> </li>--}}
{{--                            <li> <a href="{{route('categories.index')}}">مكونات الحافلة</a> </li>--}}
{{--                            <li> <a href="{{route('issues.index')}}">مشكلات مكونات الحافلة</a> </li>--}}
{{--                            <li> <a href="{{route('efficiencyFuels.index')}}">الوقود والكفاءة الأوتوماتيكي</a> </li>--}}
{{--                            <li> <a href="{{route('manuallyFuels.index')}}">استهلاك الوقود اليدوي</a> </li>--}}
{{--                        </ul>--}}
{{--                    </li>--}}


                    <li>
                        <a href="javascript:void(0);" data-toggle="collapse" data-target="#employees">
                            <div class="pull-left"><i class="ti-user"></i><span class="right-nav-text">العنصر البشري</span></div>
                            <div class="pull-right"><i class="ti-plus"></i></div>
                            <div class="clearfix"></div>
                        </a>
                        <ul id="employees" class="collapse" data-parent="#sidebarnav">
                            <li> <a href="{{route('getAllEmployees',3)}}">قائمة الموظفين</a></li>
                            <li> <a href="{{route('employeeSituations.index')}}">مواقف الموظفين</a></li>
                            <li> <a href="{{route('employeeJobs.index')}}">قائمة الوظائف</a> </li>
                            <li> <a href="{{route('getAllManagers',2)}}">قائمة المديرين</a></li>
                            <li> <a href="{{route('getAllDrivers')}}">قائمة السائقين</a></li>
                            <li> <a href="{{route('getAllUsers')}}">قائمة العملاء</a></li>
                        </ul>
                    </li>


                    <li>
                        <a href="javascript:void(0);" data-toggle="collapse" data-target="#reports">
                            <div class="pull-left"><i class="ti-notepad"></i><span class="right-nav-text">التقارير</span></div>
                            <div class="pull-right"><i class="ti-plus"></i></div>
                            <div class="clearfix"></div>
                        </a>
                        <ul id="reports" class="collapse" data-parent="#sidebarnav">
                            <li> <a href="{{route('searchForStation')}}">بيان الركاب والإيرادات لفترة</a></li>
                            <li> <a href="{{route('getLesMoney')}}">تقارير التدفق المالي</a></li>
                        </ul>
                    </li>





{{--                    <!-- menu title -->--}}
{{--                    <li class="mt-10 mb-10 text-muted pl-4 font-medium menu-title">Widgets, Forms & Tables </li>--}}
{{--                    <!-- menu item Widgets-->--}}
{{--                    <li>--}}
{{--                        <a href="widgets.html"><i class="ti-blackboard"></i><span class="right-nav-text">Widgets</span>--}}
{{--                            <span class="badge badge-pill badge-danger float-right mt-1">59</span> </a>--}}
{{--                    </li>--}}
{{--                    <!-- menu item Form-->--}}

{{--                    <!-- menu item table -->--}}
{{--                    <li>--}}
{{--                        <a href="javascript:void(0);" data-toggle="collapse" data-target="#table">--}}
{{--                            <div class="pull-left"><i class="ti-layout-tab-window"></i><span class="right-nav-text">data--}}
{{--                                    table</span></div>--}}
{{--                            <div class="pull-right"><i class="ti-plus"></i></div>--}}
{{--                            <div class="clearfix"></div>--}}
{{--                        </a>--}}
{{--                        <ul id="table" class="collapse" data-parent="#sidebarnav">--}}
{{--                            <li> <a href="data-html-table.html">Data html table</a> </li>--}}
{{--                            <li> <a href="data-local.html">Data local</a> </li>--}}
{{--                            <li> <a href="data-table.html">Data table</a> </li>--}}
{{--                        </ul>--}}
{{--                    </li>--}}
{{--                    <li class="mt-10 mb-10 text-muted pl-4 font-medium menu-title">More Pages</li>--}}
{{--                    <!-- menu item Custom pages-->--}}

{{--                    <!-- menu item Authentication-->--}}

{{--                    <!-- menu item maps-->--}}
{{--                    <li>--}}
{{--                        <a href="maps.html"><i class="ti-location-pin"></i><span class="right-nav-text">maps</span>--}}
{{--                            <span class="badge badge-pill badge-success float-right mt-1">06</span></a>--}}
{{--                    </li>--}}
{{--                    <!-- menu item timeline-->--}}
{{--                    <li>--}}
{{--                        <a href="timeline.html"><i class="ti-panel"></i><span class="right-nav-text">timeline</span>--}}
{{--                        </a>--}}
{{--                    </li>--}}
{{--                    <!-- menu item Multi level-->--}}
{{--                    <li>--}}
{{--                        <a href="javascript:void(0);" data-toggle="collapse" data-target="#multi-level">--}}
{{--                            <div class="pull-left"><i class="ti-layers"></i><span class="right-nav-text">Multi--}}
{{--                                    level Menu</span></div>--}}
{{--                            <div class="pull-right"><i class="ti-plus"></i></div>--}}
{{--                            <div class="clearfix"></div>--}}
{{--                        </a>--}}
{{--                        <ul id="multi-level" class="collapse" data-parent="#sidebarnav">--}}
{{--                            <li>--}}
{{--                                <a href="javascript:void(0);" data-toggle="collapse" data-target="#auth">Level--}}
{{--                                    item 1<div class="pull-right"><i class="ti-plus"></i></div>--}}
{{--                                    <div class="clearfix"></div>--}}
{{--                                </a>--}}
{{--                                <ul id="auth" class="collapse">--}}
{{--                                    <li>--}}
{{--                                        <a href="javascript:void(0);" data-toggle="collapse" data-target="#login">Level--}}
{{--                                            item 1.1<div class="pull-right"><i class="ti-plus"></i></div>--}}
{{--                                            <div class="clearfix"></div>--}}
{{--                                        </a>--}}
{{--                                        <ul id="login" class="collapse">--}}
{{--                                            <li>--}}
{{--                                                <a href="javascript:void(0);" data-toggle="collapse"--}}
{{--                                                    data-target="#invoice">level item 1.1.1<div class="pull-right"><i--}}
{{--                                                            class="ti-plus"></i></div>--}}
{{--                                                    <div class="clearfix"></div>--}}
{{--                                                </a>--}}
{{--                                                <ul id="invoice" class="collapse">--}}
{{--                                                    <li> <a href="#">level item 1.1.1.1</a> </li>--}}
{{--                                                    <li> <a href="#">level item 1.1.1.2</a> </li>--}}
{{--                                                </ul>--}}
{{--                                            </li>--}}
{{--                                        </ul>--}}
{{--                                    </li>--}}
{{--                                    <li> <a href="#">level item 1.2</a> </li>--}}
{{--                                </ul>--}}
{{--                            </li>--}}
{{--                            <li>--}}
{{--                                <a href="javascript:void(0);" data-toggle="collapse" data-target="#error">level--}}
{{--                                    item 2<div class="pull-right"><i class="ti-plus"></i></div>--}}
{{--                                    <div class="clearfix"></div>--}}
{{--                                </a>--}}
{{--                                <ul id="error" class="collapse">--}}
{{--                                    <li> <a href="#">level item 2.1</a> </li>--}}
{{--                                    <li> <a href="#">level item 2.2</a> </li>--}}
{{--                                </ul>--}}
{{--                            </li>--}}
{{--                        </ul>--}}
{{--                    </li>--}}
                </ul>
            </div>
        </div>

        <!-- Left Sidebar End-->

        <!--=================================
