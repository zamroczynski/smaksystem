interface TimestampsModel {
    created_at: string;
    updated_at: string
    deleted_at?: string | null;
}

export interface User extends TimestampsModel {
    id: number;
    login: string;
    name: string;
    email: string;
    email_verified_at: string | null;
    role?: string;
}

export interface Role extends TimestampsModel {
    id: number;
    name: string;
    guard_name?: string;
    is_assigned_to_users?: boolean;
}

export interface Permission extends TimestampsModel {
    id: number;
    name: string;
    guard_name?: string;
}

export interface Preference extends TimestampsModel {
    id: number;
    description?: string | number;
    date_from: string;
    date_to: string;
    is_active: boolean;
    availability: boolean;
}

export interface Schedule extends TimestampsModel {
    id: number;
    name: string;
    period_start_date: string;
    status: 'draft' | 'published' | 'archived';
}

export interface ShiftTemplate extends TimestampsModel {
    id: number;
    name: string;
    time_from: string; 
    time_to: string;  
    duration_hours: number;
    required_staff_count: number; 
}

export interface CalculationRule {
    base_type: 'event' | 'holiday';
    base_event: 'easter' | null;
    base_holiday_id: number | null;
    offset: number;
}

export interface Holiday extends TimestampsModel {
    id: number;
    name: string;
    date: string | null;
    day_month: string | null;
    calculation_rule: CalculationRule | null;
}

export interface ProductType extends TimestampsModel {
    id: number;
    name: string;
}

export interface Category extends TimestampsModel {
    id: number;
    name: string;
}

export interface UnitOfMeasure extends TimestampsModel {
    id: number;
    name: string;
    symbol: string;
}

export interface VatRate extends TimestampsModel {
    id: number;
    name: string;
    rate: number;
}

export interface Product extends TimestampsModel {
    id: number;
    name: string;
    sku: string | null;
    description: string | null;
    product_type_id: number;
    category_id: number;
    unit_of_measure_id: number;
    vat_rate_id: number;
    is_sellable: boolean;
    is_inventoried: boolean;
    selling_price: number | null;
    default_purchase_price: number | null;
    
    category_name?: string;
    unit_symbol?: string;
}

export interface RecipeIngredient {
    product_id: number;
    quantity: number;
    unit_of_measure_id: number;
}

export interface Recipe extends TimestampsModel {
    id: number;
    name: string;
    product_name: string;
    yield_quantity: number;
    yield_unit_name: string;
}
