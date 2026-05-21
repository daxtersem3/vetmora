<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\HistorialMedico;
use App\Models\Mascota;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Livewire\Component;

class HistorialModalTable extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public $recordId;
    public $recordType;

    public function mount($recordId, $recordType)
    {
        $this->recordId = $recordId;
        $this->recordType = $recordType;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                HistorialMedico::query()
                    ->whereHas('cita', function ($query) {
                        if ($this->recordType === 'Cliente') {
                            $query->where('cliente_id', $this->recordId);
                        } else {
                            $query->where('mascota_id', $this->recordId);
                        }
                    })
            )
            ->columns([
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('cita.mascota.nombre')
                    ->label('Mascota')
                    ->visible(fn () => $this->recordType === 'Cliente')
                    ->searchable(),
                TextColumn::make('cita.veterinario.name')
                    ->label('Veterinario')
                    ->searchable(),
                TextColumn::make('diagnostico')
                    ->label('Diagnóstico')
                    ->limit(40),
            ])
            ->defaultSort('fecha', 'desc')
            ->actions([
                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->url(fn ($record) => route('historial-medico.pdf', $record))
                    ->openUrlInNewTab(),
            ])
            ->paginated(false);
    }

    public function render()
    {
        return view('livewire.historial-modal-table');
    }
}
