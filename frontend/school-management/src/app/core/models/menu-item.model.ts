export interface MenuItem {
  icon: string;
  label: string;
  route: string;
  exact?: boolean;
  badge?: number;
  badgeColor?: 'primary' | 'success' | 'warning' | 'error';
  children?: MenuItem[];
}
