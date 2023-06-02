package pagination

const MaxPageSize = 500
const DefaultPageSize = 25

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
	}
	return p.page
}

func (p *Pagination) GetPageSize() int {
	if p.pageSize > MaxPageSize {
		p.pageSize = MaxPageSize
	} else if p.pageSize == 0 {
		p.pageSize = DefaultPageSize
	}
	return p.pageSize
}
