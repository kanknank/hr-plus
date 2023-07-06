<?php
namespace App\Models;

class Company extends \Illuminate\Database\Eloquent\Model {
    public $table = 'app_companys';

    protected $guarded = ['id'];
    
    protected $casts = [
        'createdon' => 'datetime:d.m.Y',
    ];

    protected $appends = [
        'vacancy_count',
        'author'
    ];

    public function getVacancyCountAttribute() {
        return \App\Models\Vacancy::where('company_id', $this->id)->count();
    }

    public function getAuthorAttribute() {
        return $this->user;
    }
}