package pagination

const MaxPageSize = 500

type Pagination struct {
	page     int
	pageSize int
}

func NewPagination(page, pageSize int32) *Pagination {
	return &Pagination{
		page:     int(page),
		pageSize: int(pageSize),
	}
}

func (p *Pagination) GetOffset() int {
	if p.page > 0 {
		return (p.page - 1) * p.GetPageSize()
	} else if p.page < 0 {
		return p.page
	}
	return 0
}

func (p *Pagination) GetPageSize() int {
	if p.pageSize <= 0 {
		p.pageSize = 25
	} else if p.pageSize > MaxPageSize {
		p.pageSize = MaxPageSize
	}
	return p.pageSize
}
