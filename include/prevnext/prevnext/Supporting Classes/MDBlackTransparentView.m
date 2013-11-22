//
//  MDBlackTransparentView.m
//  Borderless Window
//
//  Created by Mark Douma on 12/15/2010.
//  Copyright 2010 Mark Douma LLC. All rights reserved.
//

#import "MDBlackTransparentView.h"


@implementation MDBlackTransparentView


- (id)initWithFrame:(NSRect)frame {
    if (self = [super initWithFrame:frame]) {
		
    }
    return self;
}

- (void)drawRect:(NSRect)frame {
	NSBezierPath *path = [NSBezierPath bezierPathWithRoundedRect:frame xRadius:12.0 yRadius:12.0];
	[[NSColor colorWithCalibratedWhite:0.0 alpha:0.4] set];
	[path fill];
}

@end
