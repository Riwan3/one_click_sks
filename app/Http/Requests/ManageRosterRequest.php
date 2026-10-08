<?php

namespace App\Http\Requests;

/** Aksi Roster V2 tanpa body (hapus, unduh template, dsb): cukup izin kelola. */
class ManageRosterRequest extends RosterManageRequest {}
