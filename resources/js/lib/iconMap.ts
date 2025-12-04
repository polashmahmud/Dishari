import {
    LayoutDashboard, Users, Settings, FileText, Home, Mail, Calendar, Star,
    Shield, Lock, CreditCard, ShoppingCart, BarChart, PieChart, Activity,
    Globe, Server, Database, Ban, Bell, Book, Box, Briefcase, Camera, Check,
    ChevronDown, ChevronRight, Circle, Clipboard, Clock, Cloud, Code, Cog,
    Command, Compass, Copy, Cpu, Delete, Disc,
    DollarSign, Download, Edit, Eye, EyeOff, File, Filter, Flag, Folder,
    Gift, Grid, HardDrive, Hash, Heart, Image, Inbox, Info, Key, Layers,
    Layout, LifeBuoy, Link, List, Loader, LogOut, Map, MapPin, Maximize,
    Menu, MessageCircle, MessageSquare, Mic, Minimize, Minus, Monitor,
    Moon, MoreHorizontal, MoreVertical, MousePointer, Move, Music, Navigation,
    Package, Paperclip, Pause, PenTool, Phone, Play, Power, Printer, Radio,
    RefreshCw, Repeat, RotateCcw, RotateCw, Rss, Save, Scissors,
    Search, Send, Share, Share2, ShieldOff, Sidebar, Slash, Sliders, Smartphone,
    Smile, Speaker, Square, Sun, Table, Tag, Target, Terminal, Thermometer,
    ThumbsDown, ThumbsUp, ToggleLeft, ToggleRight, Trash, Trash2,
    TrendingDown, TrendingUp, Triangle, Truck, Tv, Type, Umbrella, Unlock,
    Upload, User, UserCheck, UserMinus, UserPlus, UserX, Video, Voicemail,
    Volume, Volume1, Volume2, VolumeX, Watch, Wifi, WifiOff, Wind, X,
    XCircle, XOctagon, XSquare, Zap, ZapOff, ZoomIn, ZoomOut, Plus, BookOpen
} from 'lucide-vue-next';

export const iconMap: Record<string, any> = {
    LayoutDashboard, Users, Settings, FileText, Home, Mail, Calendar, Star,
    Shield, Lock, CreditCard, ShoppingCart, BarChart, PieChart, Activity,
    Globe, Server, Database, Ban, Bell, Book, Box, Briefcase, Camera, Check,
    ChevronDown, ChevronRight, Circle, Clipboard, Clock, Cloud, Code, Cog,
    Command, Compass, Copy, Cpu, Delete, Disc,
    DollarSign, Download, Edit, Eye, EyeOff, File, Filter, Flag, Folder,
    Gift, Grid, HardDrive, Hash, Heart, Image, Inbox, Info, Key, Layers,
    Layout, LifeBuoy, Link, List, Loader, LogOut, Map, MapPin, Maximize,
    Menu, MessageCircle, MessageSquare, Mic, Minimize, Minus, Monitor,
    Moon, MoreHorizontal, MoreVertical, MousePointer, Move, Music, Navigation,
    Package, Paperclip, Pause, PenTool, Phone, Play, Power, Printer, Radio,
    RefreshCw, Repeat, RotateCcw, RotateCw, Rss, Save, Scissors,
    Search, Send, Share, Share2, ShieldOff, Sidebar, Slash, Sliders, Smartphone,
    Smile, Speaker, Square, Sun, Table, Tag, Target, Terminal, Thermometer,
    ThumbsDown, ThumbsUp, ToggleLeft, ToggleRight, Trash, Trash2,
    TrendingDown, TrendingUp, Triangle, Truck, Tv, Type, Umbrella, Unlock,
    Upload, User, UserCheck, UserMinus, UserPlus, UserX, Video, Voicemail,
    Volume, Volume1, Volume2, VolumeX, Watch, Wifi, WifiOff, Wind, X,
    XCircle, XOctagon, XSquare, Zap, ZapOff, ZoomIn, ZoomOut, Plus, BookOpen
};

export const iconOptions = Object.keys(iconMap).map(key => ({
    name: key,
    component: iconMap[key]
}));
