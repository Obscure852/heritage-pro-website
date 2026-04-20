<div class="subnav-links module-nav-links">
    <a href="{{ route('house.index') }}" class="btn {{ ($current ?? '') === 'manager' ? 'btn-primary' : 'btn-light' }}">
        <i class="fas fa-home me-1"></i> Manager
    </a>
    <a href="{{ route('house.house-list') }}"
        class="btn {{ ($current ?? '') === 'list-report' ? 'btn-primary' : 'btn-light' }}">
        <i class="fas fa-chart-pie me-1"></i> House List
    </a>
    <a href="{{ route('house.students-house-list') }}"
        class="btn {{ ($current ?? '') === 'allocations-report' ? 'btn-primary' : 'btn-light' }}">
        <i class="fas fa-user-friends me-1"></i> Student Allocations
    </a>
</div>
